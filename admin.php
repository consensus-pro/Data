<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - 账号管理系统</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Microsoft YaHei', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        /* 侧边栏 */
        .sidebar {
            position: fixed;
            left: 0; top: 0; bottom: 0;
            width: 260px;
            background: #1a1a2e;
            color: #a0a0b0;
            padding: 25px 0;
            overflow-y: auto;
            z-index: 100;
        }
        .sidebar-brand {
            padding: 0 25px 25px;
            border-bottom: 1px solid #2a2a3e;
            margin-bottom: 20px;
        }
        .sidebar-brand h2 {
            color: white;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-brand p {
            font-size: 12px;
            margin-top: 5px;
            color: #666;
        }
        .nav-section {
            padding: 0 15px;
            margin-bottom: 15px;
        }
        .nav-section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #555;
            padding: 10px 10px 5px;
            font-weight: 600;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
            margin-bottom: 3px;
        }
        .nav-item:hover, .nav-item.active {
            background: #667eea;
            color: white;
        }
        .nav-item .icon { font-size: 18px; width: 24px; text-align: center; }

        /* 主内容区 */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
        }
        .topbar {
            background: white;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .breadcrumb { color: #888; font-size: 14px; }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .topbar-btn {
            width: 40px; height: 40px;
            border-radius: 10px;
            border: none;
            background: #f5f7fa;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.2s;
        }
        .topbar-btn:hover { background: #e8ecf1; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .user-info:hover { background: #f5f7fa; }
        .user-avatar-small {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        .user-name { font-size: 14px; font-weight: 600; color: #333; }
        .user-role { font-size: 12px; color: #999; }

        .content {
            padding: 30px;
        }

        /* 统计卡片 */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .dash-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .dash-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }
        .dash-icon.blue { background: #e3f2fd; }
        .dash-icon.green { background: #e8f5e9; }
        .dash-icon.orange { background: #fff3e0; }
        .dash-icon.red { background: #ffebee; }
        .dash-info h4 { font-size: 28px; color: #333; margin-bottom: 3px; }
        .dash-info p { color: #999; font-size: 14px; }

        /* 用户表格 */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title { font-size: 18px; font-weight: 700; color: #333; }
        .card-tools { display: flex; gap: 10px; align-items: center; }
        .search-box {
            position: relative;
        }
        .search-box input {
            padding: 10px 15px 10px 38px;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            font-size: 14px;
            width: 250px;
            outline: none;
            transition: all 0.2s;
        }
        .search-box input:focus { border-color: #667eea; }
        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
        }
        .btn-sm {
            padding: 10px 18px;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary-sm {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .btn-primary-sm:hover { opacity: 0.9; }
        .btn-success-sm { background: #27ae60; color: white; }
        .btn-danger-sm { background: #e74c3c; color: white; }
        .btn-warning-sm { background: #f39c12; color: white; }

        .table-container { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th {
            background: #fafbfc;
            color: #666;
            font-weight: 600;
            text-align: left;
            padding: 14px 20px;
            border-bottom: 2px solid #f0f0f0;
            white-space: nowrap;
        }
        td {
            padding: 14px 20px;
            border-bottom: 1px solid #f5f5f5;
            color: #555;
        }
        tr:hover { background: #fafbfc; }
        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar-td {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }
        .user-name-td { font-weight: 600; color: #333; }
        .user-email-td { font-size: 12px; color: #999; }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-admin { background: #e3f2fd; color: #1976d2; }
        .badge-user { background: #e8f5e9; color: #388e3c; }
        .badge-active { background: #e8f5e9; color: #2e7d32; }
        .badge-inactive { background: #ffebee; color: #c62828; }
        .action-btns-td { display: flex; gap: 6px; }
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .action-btn:hover { transform: translateY(-1px); }
        .action-btn.edit { background: #e3f2fd; color: #1976d2; }
        .action-btn.delete { background: #ffebee; color: #c62828; }
        .action-btn.reset { background: #fff3e0; color: #e65100; }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 20px;
        }
        .page-btn {
            padding: 8px 14px;
            border: 2px solid #e8e8e8;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }
        .page-btn:hover, .page-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
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
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalIn 0.3s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal h3 { margin-bottom: 20px; color: #333; font-size: 20px; }
        .modal .form-group { margin-bottom: 18px; }
        .modal .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }
        .modal .form-group input, .modal .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e8e8e8;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }
        .modal .form-group input:focus, .modal .form-group select:focus {
            border-color: #667eea;
        }
        .modal-btns {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }
        .modal-btns .btn { flex: 1; justify-content: center; padding: 12px; font-size: 14px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn-outline { background: white; color: #667eea; border: 2px solid #667eea; }
        .btn-outline:hover { background: #667eea; color: white; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-danger { background: #e74c3c; color: white; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state .icon { font-size: 48px; margin-bottom: 15px; }

        /* Toast */
        .toast-container {
            position: fixed;
            top: 20px; right: 20px;
            z-index: 3000;
        }
        .toast {
            background: white;
            border-radius: 12px;
            padding: 16px 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: toastIn 0.3s ease;
            min-width: 280px;
        }
        @keyframes toastIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast.success { border-left: 4px solid #27ae60; }
        .toast.error { border-left: 4px solid #e74c3c; }
        .toast-icon { font-size: 20px; }
        .toast.success .toast-icon { color: #27ae60; }
        .toast.error .toast-icon { color: #e74c3c; }
        .toast-msg { font-size: 14px; color: #333; }

        /* 响应式 */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .dashboard-stats { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <!-- 侧边栏 -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h2>⚙️ 管理后台</h2>
            <p>远程用户账号管理系统</p>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">主菜单</div>
            <div class="nav-item active" onclick="showSection('users')">
                <span class="icon">👥</span> 用户管理
            </div>
            <div class="nav-item" onclick="showSection('stats')">
                <span class="icon">📊</span> 数据统计
            </div>
            <div class="nav-item" onclick="showSection('logs')">
                <span class="icon">📋</span> 操作日志
            </div>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">系统</div>
            <div class="nav-item" onclick="location.href='index.php'">
                <span class="icon">🏠</span> 返回前台
            </div>
            <div class="nav-item" onclick="logout()">
                <span class="icon">🚪</span> 退出登录
            </div>
        </div>
    </aside>

    <div class="main-wrapper">
        <!-- 顶部栏 -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-btn" onclick="toggleSidebar()">☰</button>
                <span class="breadcrumb">管理后台 / 用户管理</span>
            </div>
            <div class="topbar-right">
                <button class="topbar-btn">🔔</button>
                <div class="user-info">
                    <div class="user-avatar-small"><?php echo mb_substr($_SESSION['user']['username'], 0, 1); ?></div>
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></div>
                        <div class="user-role">管理员</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="content">
            <!-- 统计概览 -->
            <div class="dashboard-stats">
                <div class="dash-card">
                    <div class="dash-icon blue">👥</div>
                    <div class="dash-info">
                        <h4 id="statTotal">0</h4>
                        <p>总用户数</p>
                    </div>
                </div>
                <div class="dash-card">
                    <div class="dash-icon green">✅</div>
                    <div class="dash-info">
                        <h4 id="statActive">0</h4>
                        <p>活跃用户</p>
                    </div>
                </div>
                <div class="dash-card">
                    <div class="dash-icon orange">📅</div>
                    <div class="dash-info">
                        <h4 id="statToday">0</h4>
                        <p>今日注册</p>
                    </div>
                </div>
                <div class="dash-card">
                    <div class="dash-icon red">🔐</div>
                    <div class="dash-info">
                        <h4 id="statAdmin">0</h4>
                        <p>管理员数</p>
                    </div>
                </div>
            </div>

            <!-- 用户管理表格 -->
            <div class="card" id="usersSection">
                <div class="card-header">
                    <h3 class="card-title">👥 用户账号管理</h3>
                    <div class="card-tools">
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="搜索用户名或邮箱..." oninput="searchUsers()">
                        </div>
                        <button class="btn-sm btn-primary-sm" onclick="openAddModal()">➕ 新增用户</button>
                        <button class="btn-sm btn-success-sm" onclick="exportData()">📥 导出</button>
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>用户信息</th>
                                <th>角色</th>
                                <th>状态</th>
                                <th>注册时间</th>
                                <th>最后登录</th>
                                <th>登录次数</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <!-- JS动态填充 -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination" id="pagination">
                    <!-- JS动态填充 -->
                </div>
            </div>
        </div>
    </div>

    <!-- 新增/编辑用户模态框 -->
    <div class="modal-overlay" id="userModal">
        <div class="modal">
            <h3 id="modalTitle">➕ 新增用户</h3>
            <input type="hidden" id="editUserId">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" id="modalUsername" placeholder="输入用户名">
            </div>
            <div class="form-group">
                <label>邮箱</label>
                <input type="email" id="modalEmail" placeholder="输入邮箱">
            </div>
            <div class="form-group">
                <label>密码 <small id="pwdHint" style="color:#999">（留空则不修改）</small></label>
                <input type="password" id="modalPassword" placeholder="设置密码">
            </div>
            <div class="form-group">
                <label>角色</label>
                <select id="modalRole">
                    <option value="user">普通用户</option>
                    <option value="admin">管理员</option>
                </select>
            </div>
            <div class="form-group">
                <label>状态</label>
                <select id="modalStatus">
                    <option value="active">正常</option>
                    <option value="inactive">禁用</option>
                </select>
            </div>
            <div class="modal-btns">
                <button class="btn btn-outline" onclick="closeModal('userModal')">取消</button>
                <button class="btn btn-primary" onclick="saveUser()">保存</button>
            </div>
        </div>
    </div>

    <!-- 删除确认模态框 -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal" style="max-width: 400px;">
            <h3>⚠️ 确认删除</h3>
            <p style="color: #666; margin-bottom: 20px;">确定要删除用户 <strong id="deleteUsername"></strong> 吗？此操作不可撤销！</p>
            <input type="hidden" id="deleteUserId">
            <div class="modal-btns">
                <button class="btn btn-outline" onclick="closeModal('deleteModal')">取消</button>
                <button class="btn btn-danger" onclick="confirmDelete()">确认删除</button>
            </div>
        </div>
    </div>

    <!-- Toast容器 -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        let allUsers = [];
        let currentPage = 1;
        const pageSize = 10;
        let editingId = null;

        // 初始化
        document.addEventListener('DOMContentLoaded', () => {
            loadUsers();
            loadStats();
        });

        async function loadUsers() {
            try {
                const res = await fetch('api.php?action=getUsers');
                const data = await res.json();
                if (data.success) {
                    allUsers = data.users;
                    renderTable();
                }
            } catch(e) {
                showToast('加载用户数据失败', 'error');
            }
        }

        async function loadStats() {
            try {
                const res = await fetch('api.php?action=getStats');
                const data = await res.json();
                if (data.success) {
                    document.getElementById('statTotal').textContent = data.stats.total;
                    document.getElementById('statActive').textContent = data.stats.active;
                    document.getElementById('statToday').textContent = data.stats.today;
                    document.getElementById('statAdmin').textContent = data.stats.admin;
                }
            } catch(e) {}
        }

        function renderTable() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            let filtered = allUsers;
            if (search) {
                filtered = allUsers.filter(u => 
                    u.username.toLowerCase().includes(search) || 
                    u.email.toLowerCase().includes(search)
                );
            }

            const totalPages = Math.ceil(filtered.length / pageSize);
            const start = (currentPage - 1) * pageSize;
            const pageUsers = filtered.slice(start, start + pageSize);

            const tbody = document.getElementById('userTableBody');
            if (pageUsers.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><div class="icon">📭</div><p>暂无用户数据</p></div></td></tr>`;
            } else {
                tbody.innerHTML = pageUsers.map(u => `
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar-td">${u.username.charAt(0).toUpperCase()}</div>
                                <div>
                                    <div class="user-name-td">${escapeHtml(u.username)}</div>
                                    <div class="user-email-td">${escapeHtml(u.email)}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-${u.role}">${u.role === 'admin' ? '管理员' : '普通用户'}</span></td>
                        <td><span class="badge badge-${u.status}">${u.status === 'active' ? '正常' : '禁用'}</span></td>
                        <td>${u.created_at}</td>
                        <td>${u.last_login || '-'}</td>
                        <td>${u.login_count || 0}</td>
                        <td>
                            <div class="action-btns-td">
                                <button class="action-btn edit" onclick="openEditModal(${u.id})">编辑</button>
                                <button class="action-btn reset" onclick="resetPassword(${u.id}, '${escapeHtml(u.username)}')">重置密码</button>
                                <button class="action-btn delete" onclick="openDeleteModal(${u.id}, '${escapeHtml(u.username)}')">删除</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }

            // 分页
            let pagesHtml = '';
            for (let i = 1; i <= totalPages; i++) {
                pagesHtml += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goPage(${i})">${i}</button>`;
            }
            document.getElementById('pagination').innerHTML = pagesHtml;
        }

        function searchUsers() {
            currentPage = 1;
            renderTable();
        }

        function goPage(p) {
            currentPage = p;
            renderTable();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function openAddModal() {
            editingId = null;
            document.getElementById('modalTitle').textContent = '➕ 新增用户';
            document.getElementById('editUserId').value = '';
            document.getElementById('modalUsername').value = '';
            document.getElementById('modalEmail').value = '';
            document.getElementById('modalPassword').value = '';
            document.getElementById('modalPassword').placeholder = '设置密码';
            document.getElementById('pwdHint').textContent = '';
            document.getElementById('modalRole').value = 'user';
            document.getElementById('modalStatus').value = 'active';
            document.getElementById('userModal').classList.add('show');
        }

        function openEditModal(id) {
            const user = allUsers.find(u => u.id == id);
            if (!user) return;
            editingId = id;
            document.getElementById('modalTitle').textContent = '✏️ 编辑用户';
            document.getElementById('editUserId').value = id;
            document.getElementById('modalUsername').value = user.username;
            document.getElementById('modalEmail').value = user.email;
            document.getElementById('modalPassword').value = '';
            document.getElementById('modalPassword').placeholder = '留空则不修改';
            document.getElementById('pwdHint').textContent = '（留空则不修改密码）';
            document.getElementById('modalRole').value = user.role;
            document.getElementById('modalStatus').value = user.status;
            document.getElementById('userModal').classList.add('show');
        }

        async function saveUser() {
            const username = document.getElementById('modalUsername').value.trim();
            const email = document.getElementById('modalEmail').value.trim();
            const password = document.getElementById('modalPassword').value;
            const role = document.getElementById('modalRole').value;
            const status = document.getElementById('modalStatus').value;

            if (!username || !email) {
                showToast('请填写完整信息', 'error');
                return;
            }
            if (!editingId && !password) {
                showToast('新增用户必须设置密码', 'error');
                return;
            }

            const payload = { username, email, role, status };
            if (password) payload.password = password;
            if (editingId) payload.id = editingId;

            try {
                const action = editingId ? 'updateUser' : 'addUser';
                const res = await fetch(`api.php?action=${action}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showToast(editingId ? '用户更新成功' : '用户创建成功', 'success');
                    closeModal('userModal');
                    loadUsers();
                    loadStats();
                } else {
                    showToast(data.message || '操作失败', 'error');
                }
            } catch(e) {
                showToast('网络错误', 'error');
            }
        }

        function openDeleteModal(id, username) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUsername').textContent = username;
            document.getElementById('deleteModal').classList.add('show');
        }

        async function confirmDelete() {
            const id = document.getElementById('deleteUserId').value;
            try {
                const res = await fetch('api.php?action=deleteUser', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id})
                });
                const data = await res.json();
                if (data.success) {
                    showToast('用户已删除', 'success');
                    closeModal('deleteModal');
                    loadUsers();
                    loadStats();
                } else {
                    showToast(data.message || '删除失败', 'error');
                }
            } catch(e) {
                showToast('网络错误', 'error');
            }
        }

        async function resetPassword(id, username) {
            const newPwd = prompt(`重置用户 "${username}" 的密码：\n请输入新密码（至少6位）：`);
            if (!newPwd) return;
            if (newPwd.length < 6) {
                showToast('密码至少6位', 'error');
                return;
            }
            try {
                const res = await fetch('api.php?action=resetPassword', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id, password: newPwd})
                });
                const data = await res.json();
                if (data.success) {
                    showToast('密码重置成功', 'success');
                } else {
                    showToast(data.message || '重置失败', 'error');
                }
            } catch(e) {
                showToast('网络错误', 'error');
            }
        }

        function exportData() {
            const csv = [
                ['ID', '用户名', '邮箱', '角色', '状态', '注册时间', '最后登录', '登录次数'],
                ...allUsers.map(u => [u.id, u.username, u.email, u.role, u.status, u.created_at, u.last_login || '-', u.login_count || 0])
            ].map(row => row.map(c => `"${c}"`).join(',')).join('\n');

            const blob = new Blob(['\ufeff' + csv], {type: 'text/csv;charset=utf-8;'});
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `users_export_${new Date().toISOString().slice(0,10)}.csv`;
            link.click();
            showToast('数据导出成功', 'success');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
        }

        function showToast(msg, type) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <span class="toast-icon">${type === 'success' ? '✅' : '❌'}</span>
                <span class="toast-msg">${msg}</span>
            `;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        function logout() {
            fetch('api.php?action=logout', {method: 'POST'}).then(() => {
                location.href = 'login.html';
            });
        }

        function showSection(sec) {
            if (sec === 'users') {
                document.querySelector('.breadcrumb').textContent = '管理后台 / 用户管理';
            } else if (sec === 'stats') {
                showToast('数据统计功能开发中...', 'success');
            } else if (sec === 'logs') {
                showToast('操作日志功能开发中...', 'success');
            }
        }
    </script>
</body>
</html>