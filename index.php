<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户中心 - 账号管理系统</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Microsoft YaHei', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        /* 顶部导航 */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0 30px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand svg { width: 28px; height: 28px; }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .nav-item {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .nav-item:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        .user-menu {
            position: relative;
        }
        .user-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .dropdown {
            position: absolute;
            top: 50px; right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            min-width: 180px;
            padding: 8px 0;
            display: none;
            overflow: hidden;
        }
        .dropdown.show { display: block; }
        .dropdown-item {
            padding: 12px 20px;
            color: #555;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .dropdown-item:hover {
            background: #f5f7fa;
            color: #667eea;
        }
        .dropdown-item.danger { color: #e74c3c; }
        .dropdown-item.danger:hover { background: #ffebee; }
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 8px 0;
        }

        /* 主内容区 */
        .main-content {
            padding: 94px 30px 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* 欢迎卡片 */
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .welcome-card::after {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .welcome-card h1 {
            font-size: 28px;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        .welcome-card p {
            opacity: 0.9;
            font-size: 15px;
            position: relative;
            z-index: 1;
        }

        /* 统计卡片 */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 15px;
        }
        .stat-icon.blue { background: #e3f2fd; }
        .stat-icon.green { background: #e8f5e9; }
        .stat-icon.orange { background: #fff3e0; }
        .stat-icon.purple { background: #f3e5f5; }
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #999;
            font-size: 14px;
        }

        /* 账号信息卡片 */
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .info-section { grid-template-columns: 1fr; }
        }
        .info-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .info-card h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #888; font-size: 14px; }
        .info-value { color: #333; font-weight: 500; font-size: 14px; }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-inactive { background: #ffebee; color: #c62828; }

        /* 操作按钮 */
        .action-btns {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.4);
        }
        .btn-outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }

        /* 模态框 */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            background: white;
            border-radius: 20px;
            padding: 30px;
            width: 90%;
            max-width: 450px;
            animation: modalIn 0.3s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal h3 {
            margin-bottom: 20px;
            color: #333;
        }
        .modal .form-group {
            margin-bottom: 15px;
        }
        .modal .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #555;
            font-size: 14px;
        }
        .modal .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
        }
        .modal .form-group input:focus {
            border-color: #667eea;
        }
        .modal-btns {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .modal-btns .btn { flex: 1; justify-content: center; }

        /* 底部 */
        .footer {
            text-align: center;
            padding: 30px;
            color: #aaa;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
            账号管理系统
        </div>
        <div class="navbar-right">
            <span class="nav-item">🔔 通知</span>
            <div class="user-menu">
                <div class="user-avatar" onclick="toggleDropdown()">
                    <?php echo mb_substr($user['username'], 0, 1); ?>
                </div>
                <div class="dropdown" id="userDropdown">
                    <div class="dropdown-item">👤 <?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="dropdown-item">📧 <?php echo htmlspecialchars($user['email']); ?></div>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item" onclick="openEditModal()">✏️ 修改资料</div>
                    <div class="dropdown-item" onclick="openPasswordModal()">🔒 修改密码</div>
                    <?php if ($user['role'] === 'admin'): ?>
                    <div class="dropdown-item" onclick="location.href='admin.php'">⚙️ 管理后台</div>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item danger" onclick="logout()">🚪 退出登录</div>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="welcome-card">
            <h1>欢迎回来，<?php echo htmlspecialchars($user['username']); ?>！</h1>
            <p>今天是 <?php echo date('Y年m月d日 l'); ?>，祝您使用愉快</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📅</div>
                <div class="stat-value"><?php echo floor((time() - strtotime($user['created_at'])) / 86400) + 1; ?></div>
                <div class="stat-label">注册天数</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">⏰</div>
                <div class="stat-value"><?php echo date('H:i', strtotime($user['last_login'] ?? $user['created_at'])); ?></div>
                <div class="stat-label">最后登录时间</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">🎯</div>
                <div class="stat-value"><?php echo $user['login_count'] ?? 1; ?></div>
                <div class="stat-label">登录次数</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">🔐</div>
                <div class="stat-value"><?php echo ucfirst($user['role']); ?></div>
                <div class="stat-label">账号权限</div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-card">
                <h3>📋 账号信息</h3>
                <div class="info-row">
                    <span class="info-label">用户ID</span>
                    <span class="info-value">#<?php echo $user['id']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">用户名</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">邮箱</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">注册时间</span>
                    <span class="info-value"><?php echo $user['created_at']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">账号状态</span>
                    <span class="status-badge status-active">正常</span>
                </div>
            </div>

            <div class="info-card">
                <h3>⚡ 快捷操作</h3>
                <div style="margin-bottom: 20px; color: #666; font-size: 14px; line-height: 1.6;">
                    您可以在这里快速管理您的账号设置，或前往管理后台进行更多操作。
                </div>
                <div class="action-btns">
                    <button class="btn btn-primary" onclick="openEditModal()">✏️ 编辑资料</button>
                    <button class="btn btn-outline" onclick="openPasswordModal()">🔒 修改密码</button>
                    <?php if ($user['role'] === 'admin'): ?>
                    <button class="btn btn-outline" onclick="location.href='admin.php'">⚙️ 管理后台</button>
                    <?php endif; ?>
                    <button class="btn btn-danger" onclick="logout()">🚪 退出登录</button>
                </div>
            </div>
        </div>
    </main>

    <div class="footer">
        © 2026 账号管理系统 · 安全守护每一刻
    </div>

    <!-- 修改资料模态框 -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <h3>✏️ 修改资料</h3>
            <div class="form-group">
                <label>用户名</label>
                <input type="text" id="editUsername" value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>
            <div class="form-group">
                <label>邮箱</label>
                <input type="email" id="editEmail" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            <div class="modal-btns">
                <button class="btn btn-outline" onclick="closeModal('editModal')">取消</button>
                <button class="btn btn-primary" onclick="saveProfile()">保存</button>
            </div>
        </div>
    </div>

    <!-- 修改密码模态框 -->
    <div class="modal-overlay" id="passwordModal">
        <div class="modal">
            <h3>🔒 修改密码</h3>
            <div class="form-group">
                <label>当前密码</label>
                <input type="password" id="oldPassword" placeholder="输入当前密码">
            </div>
            <div class="form-group">
                <label>新密码</label>
                <input type="password" id="newPassword" placeholder="设置新密码（至少6位）">
            </div>
            <div class="form-group">
                <label>确认新密码</label>
                <input type="password" id="confirmPassword" placeholder="再次输入新密码">
            </div>
            <div class="modal-btns">
                <button class="btn btn-outline" onclick="closeModal('passwordModal')">取消</button>
                <button class="btn btn-primary" onclick="savePassword()">保存</button>
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                document.getElementById('userDropdown').classList.remove('show');
            }
        });

        function openEditModal() {
            document.getElementById('editModal').classList.add('show');
        }
        function openPasswordModal() {
            document.getElementById('passwordModal').classList.add('show');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
        }

        async function saveProfile() {
            const username = document.getElementById('editUsername').value.trim();
            const email = document.getElementById('editEmail').value.trim();

            try {
                const res = await fetch('api.php?action=updateProfile', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username, email})
                });
                const data = await res.json();
                if (data.success) {
                    alert('资料更新成功！');
                    location.reload();
                } else {
                    alert(data.message || '更新失败');
                }
            } catch(e) {
                alert('网络错误');
            }
        }

        async function savePassword() {
            const oldPassword = document.getElementById('oldPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                alert('两次输入的密码不一致');
                return;
            }
            if (newPassword.length < 6) {
                alert('新密码至少6位');
                return;
            }

            try {
                const res = await fetch('api.php?action=changePassword', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({oldPassword, newPassword})
                });
                const data = await res.json();
                if (data.success) {
                    alert('密码修改成功！请重新登录');
                    logout();
                } else {
                    alert(data.message || '修改失败');
                }
            } catch(e) {
                alert('网络错误');
            }
        }

        async function logout() {
            sessionStorage.clear();
            await fetch('api.php?action=logout', {method: 'POST'});
            location.href = 'login.html';
        }
    </script>
</body>
</html>