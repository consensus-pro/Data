<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 数据库配置 - SQLite (无需额外配置，开箱即用)
$dbFile = __DIR__ . '/data/users.db';
$dbDir = __DIR__ . '/data';

if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 创建用户表
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'user',
        status TEXT DEFAULT 'active',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        last_login TEXT,
        login_count INTEGER DEFAULT 0
    )");

    // 创建默认管理员账号 (admin/admin123)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'admin', 'active')")
            ->execute(['admin', 'admin@example.com', $hash]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '数据库错误: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'register':
        handleRegister($pdo, $input);
        break;
    case 'login':
        handleLogin($pdo, $input);
        break;
    case 'logout':
        handleLogout();
        break;
    case 'getUsers':
        handleGetUsers($pdo);
        break;
    case 'getStats':
        handleGetStats($pdo);
        break;
    case 'addUser':
        handleAddUser($pdo, $input);
        break;
    case 'updateUser':
        handleUpdateUser($pdo, $input);
        break;
    case 'deleteUser':
        handleDeleteUser($pdo, $input);
        break;
    case 'resetPassword':
        handleResetPassword($pdo, $input);
        break;
    case 'updateProfile':
        handleUpdateProfile($pdo, $input);
        break;
    case 'changePassword':
        handleChangePassword($pdo, $input);
        break;
    default:
        echo json_encode(['success' => false, 'message' => '未知操作']);
}

function handleRegister($pdo, $input) {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $email = trim($input['email'] ?? '');

    if (empty($username) || empty($password) || empty($email)) {
        echo json_encode(['success' => false, 'message' => '请填写所有必填项']);
        return;
    }
    if (strlen($username) < 3) {
        echo json_encode(['success' => false, 'message' => '用户名至少3位']);
        return;
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => '密码至少6位']);
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => '邮箱格式不正确']);
        return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)")
            ->execute([$username, $email, $hash]);
        echo json_encode(['success' => true, 'message' => '注册成功']);
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
            if (strpos($e->getMessage(), 'username') !== false) {
                echo json_encode(['success' => false, 'message' => '用户名已被使用']);
            } else {
                echo json_encode(['success' => false, 'message' => '邮箱已被注册']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '注册失败']);
        }
    }
}

function handleLogin($pdo, $input) {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '请填写账号和密码']);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => '账号或密码错误']);
        return;
    }

    if ($user['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => '账号已被禁用，请联系管理员']);
        return;
    }

    // 更新登录信息
    $pdo->prepare("UPDATE users SET last_login = datetime('now'), login_count = login_count + 1 WHERE id = ?")
        ->execute([$user['id']]);

    // 设置会话
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'status' => $user['status'],
        'created_at' => $user['created_at'],
        'last_login' => date('Y-m-d H:i:s'),
        'login_count' => ($user['login_count'] ?? 0) + 1
    ];

    echo json_encode([
        'success' => true,
        'message' => '登录成功',
        'user' => $_SESSION['user']
    ]);
}

function handleLogout() {
    session_destroy();
    echo json_encode(['success' => true]);
}

function handleGetUsers($pdo) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => '无权限']);
        return;
    }
    $stmt = $pdo->query("SELECT id, username, email, role, status, created_at, last_login, login_count FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'users' => $users]);
}

function handleGetStats($pdo) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => '无权限']);
        return;
    }
    $total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $active = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    $today = $pdo->query("SELECT COUNT(*) FROM users WHERE date(created_at) = date('now')")->fetchColumn();
    $admin = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    echo json_encode(['success' => true, 'stats' => ['total' => $total, 'active' => $active, 'today' => $today, 'admin' => $admin]]);
}

function handleAddUser($pdo, $input) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => '无权限']);
        return;
    }
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? 'user';
    $status = $input['status'] ?? 'active';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '请填写完整信息']);
        return;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)")
            ->execute([$username, $email, $hash, $role, $status]);
        echo json_encode(['success' => true, 'message' => '用户创建成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '用户名或邮箱已存在']);
    }
}

function handleUpdateUser($pdo, $input) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => '无权限']);
        return;
    }
    $id = $input['id'] ?? 0;
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $role = $input['role'] ?? 'user';
    $status = $input['status'] ?? 'active';
    $password = $input['password'] ?? '';

    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => '请填写完整信息']);
        return;
    }

    try {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ?, status = ? WHERE id = ?")
                ->execute([$username, $email, $hash, $role, $status, $id]);
        } else {
            $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE id = ?")
                ->execute([$username, $email, $role, $status, $id]);
        }
        echo json_encode(['success' => true, 'message' => '用户更新成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '用户名或邮箱已存在']);
    }
}

function handleDeleteUser($pdo, $input) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => '无权限']);
        return;
    }
    $id = $input['id'] ?? 0;
    if ($id == ($_SESSION['user_id'] ?? 0)) {
        echo json_encode(['success' => false, 'message' => '不能删除自己']);
        return;
    }
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true, 'message' => '用户已删除']);
}

function handleResetPassword($pdo, $input) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => '无权限']);
        return;
    }
    $id = $input['id'] ?? 0;
    $password = $input['password'] ?? '';
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => '密码至少6位']);
        return;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $id]);
    echo json_encode(['success' => true, 'message' => '密码重置成功']);
}

function handleUpdateProfile($pdo, $input) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => '未登录']);
        return;
    }
    $id = $_SESSION['user_id'];
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');

    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => '请填写完整信息']);
        return;
    }

    try {
        $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?")
            ->execute([$username, $email, $id]);
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;
        echo json_encode(['success' => true, 'message' => '资料更新成功']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => '用户名或邮箱已存在']);
    }
}

function handleChangePassword($pdo, $input) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => '未登录']);
        return;
    }
    $id = $_SESSION['user_id'];
    $oldPassword = $input['oldPassword'] ?? '';
    $newPassword = $input['newPassword'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($oldPassword, $user['password'])) {
        echo json_encode(['success' => false, 'message' => '当前密码错误']);
        return;
    }

    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $id]);
    echo json_encode(['success' => true, 'message' => '密码修改成功']);
}

function isAdmin() {
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}
