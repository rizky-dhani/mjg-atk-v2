<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MJG ATK Management System - User Tutorial</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
        }

        .container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            min-height: 100vh;
            padding: 20px;
        }

        .sidebar {
            width: 300px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            position: sticky;
            top: 20px;
            height: fit-content;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            margin-right: 20px;
        }

        main {
            flex: 1;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .nav-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #007cba;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 5px;
        }

        .nav-links a {
            display: block;
            padding: 8px 12px;
            text-decoration: none;
            color: #4a5568;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .nav-links a:hover {
            background-color: #e2e8f0;
            color: #007cba;
        }

        .nav-links a.active {
            background-color: #007cba;
            color: white;
        }


        .nav-links a.active {
            background-color: #007cba;
            color: white;
        }

        header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #007cba;
            margin-bottom: 30px;
        }

        h1 {
            color: #007cba;
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        h2 {
            color: #007cba;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 30px;
            margin-bottom: 20px;
            font-size: 1.6rem;
        }

        h3 {
            color: #007cba;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .section {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .section:last-child {
            border-bottom: none;
        }

        .role-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #007cba;
            margin: 15px 0;
            border-radius: 0 4px 4px 0;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .feature-card {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .step {
            margin: 10px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
            border-left: 3px solid #007cba;
        }

        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #ffc107;
        }

        .tip {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #007cba;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f1f5f9;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .nav-item {
            display: inline-block;
            background-color: #e7f3ff;
            padding: 5px 10px;
            margin: 5px 2px;
            border-radius: 3px;
            font-size: 0.9rem;
        }

        ul, ol {
            margin: 10px 0;
            padding-left: 30px;
        }

        li {
            margin-bottom: 5px;
        }

        p {
            margin-bottom: 10px;
        }

        footer {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                position: static;
                max-height: none;
                margin-bottom: 20px;
            }

            main {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h3 class="nav-title">Table of Contents</h3>
            <ul class="nav-links">
                <li><a href="#overview">System Overview</a></li>
                <li><a href="#roles">User Roles and Permissions</a></li>
                <li><a href="#navigation">Navigation Guide</a></li>
                <li><a href="#features">Key Features</a></li>
                <li><a href="#atk-stock-requests">ATK Stock Requests</a></li>
                <li><a href="#atk-stock-usage">ATK Stock Usage</a></li>
                <li><a href="#marketing-media-stock-requests">Marketing Media Stock Requests</a></li>
                <li><a href="#marketing-media-stock-usage">Marketing Media Stock Usage</a></li>
                <li><a href="#approvals">Approval Workflow</a></li>
                <li><a href="#admin">Administrative Functions</a></li>
                <li><a href="#tips">Helpful Tips</a></li>
            </ul>
        </nav>

        <main>
            <header>
                <h1>MJG ATK Management System</h1>
                <h2>Comprehensive User Tutorial</h2>
                <p>An inventory management solution for office stationery (Alat Tulis Kantor/ATK) and marketing media items with robust approval workflows</p>
            </header>

            <div class="section" id="overview">
                <h2>System Overview</h2>
                <p>The MJG ATK Management System is a comprehensive inventory management solution built with Laravel and Filament v4. It enables organizations to efficiently manage their office supplies inventory across multiple divisions with a robust approval workflow.</p>

                <div class="feature-grid">
                    <div class="feature-card">
                        <h3>Inventory Management</h3>
                        <p>Track and manage office supplies inventory across multiple divisions</p>
                    </div>
                    <div class="feature-card">
                        <h3>Approval Workflow</h3>
                        <p>Configurable multi-step approval processes for stock requests and usages</p>
                    </div>
                    <div class="feature-card">
                        <h3>Division-Based Organization</h3>
                        <p>Structure your inventory by organizational divisions</p>
                    </div>
                </div>
            </div>

            <div class="section" id="roles">
                <h2>User Roles and Permissions</h2>
                <p>The system implements a Role-Based Access Control (RBAC) system with multiple user roles:</p>

                <div class="role-info">
                    <h3>Super Admin</h3>
                    <ul>
                        <li>Full system access</li>
                        <li>User management</li>
                        <li>Role and permission management</li>
                        <li>All administrative functions</li>
                        <li>Can access all divisions' data</li>
                    </ul>
                </div>

                <div class="role-info">
                    <h3>Admin</h3>
                    <ul>
                        <li>Manage ATK categories and items</li>
                        <li>View and manage all divisions</li>
                        <li>Approve/reject stock requests and usages</li>
                        <li>Manage approval workflows</li>
                        <li>Access only their assigned division's data</li>
                    </ul>
                </div>

                <div class="role-info">
                    <h3>Head</h3>
                    <ul>
                        <li>View division inventory</li>
                        <li>Request stock for their division</li>
                        <li>Approve/reject stock requests and usages within their authority</li>
                        <li>Access only their assigned division's data</li>
                    </ul>
                </div>

                <div class="role-info">
                    <h3>Staff</h3>
                    <ul>
                        <li>View available inventory</li>
                        <li>Request stock for their division</li>
                        <li>Access only their assigned division's data</li>
                    </ul>
                </div>

                <div class="tip">
                    <strong>Important:</strong> Your access is restricted by your role and division assignment. Contact your system administrator if you need access to different resources.
                </div>
            </div>

            <div class="section" id="navigation">
                <h2>Navigation Guide</h2>
                <p>The system is organized into several main sections accessible from the left navigation menu:</p>

                <h3>Primary Navigation</h3>
                <table>
                    <tr>
                        <th>Section</th>
                        <th>Description</th>
                        <th>Access Level</th>
                    </tr>
                    <tr>
                        <td>Alat Tulis Kantor (ATK)</td>
                        <td>Office stationery management section</td>
                        <td>All users</td>
                    </tr>
                    <tr>
                        <td>Marketing Media</td>
                        <td>Marketing materials management section</td>
                        <td>Admin/Super Admin</td>
                    </tr>
                    <tr>
                        <td>Approval Permintaan</td>
                        <td>Approval workflows for requests and usages</td>
                        <td>Approvers only</td>
                    </tr>
                    <tr>
                        <td>Settings</td>
                        <td>System configuration and item management</td>
                        <td>Admin access required</td>
                    </tr>
                </table>

                <h3>Available Menu Items</h3>
                <p>Depending on your role, you may see these navigation items:</p>
                <div>
                    <span class="nav-item">Permintaan ATK (ATK Requests)</span>
                    <span class="nav-item">Pengeluaran ATK (ATK Usages)</span>
                    <span class="nav-item">Permintaan Marketing Media</span>
                    <span class="nav-item">Pengeluaran Marketing Media</span>
                    <span class="nav-item">Item Inventaris - ATK</span>
                    <span class="nav-item">Item Inventaris - Marketing Media</span>
                </div>
            </div>

            <div class="section" id="features">
                <h2>Key Features</h2>

                <h3>ATK Management</h3>
                <ul>
                    <li><strong>ATK Categories:</strong> Organize items by category for better management</li>
                    <li><strong>ATK Items:</strong> Detailed management of individual office supply items</li>
                    <li><strong>Division Stocks:</strong> Track inventory levels per division in real-time</li>
                    <li><strong>Stock Requests:</strong> Formally request additional inventory when needed</li>
                    <li><strong>Stock Usages:</strong> Record consumption of inventory for tracking</li>
                </ul>

                <h3>Marketing Media Management</h3>
                <ul>
                    <li>Similar functionality to ATK but for marketing materials</li>
                    <li>Separate inventory tracking and approval workflows</li>
                </ul>

                <h3>Approval System</h3>
                <ul>
                    <li>Configurable approval flows for different types of requests</li>
                    <li>Multi-step approval process with role-based permissions</li>
                    <li>Audit trail of all approval decisions</li>
                </ul>
            </div>

            <div class="section" id="atk-stock-requests-usage">
                <h2>ATK Stock Requests & Usage Process</h2>

                <h3 id="atk-stock-requests">Creating ATK Stock Requests</h3>
                <p>Follow these steps to request additional ATK inventory for your division:</p>

                <div class="step">
                    <h3>Step 1: Navigate to ATK Stock Requests</h3>
                    <p>Go to the "Alat Tulis Kantor" section and click on "Permintaan ATK".</p>
                </div>

                <div class="step">
                    <h3>Step 2: Create New Request</h3>
                    <p>Click the "Create" button to start a new ATK stock request.</p>
                </div>

                <div class="step">
                    <h3>Step 3: Fill Request Details</h3>
                    <p>Enter the following information:
                    <ul>
                        <li>Request type (if applicable)</li>
                        <li>Notes (optional)</li>
                        <li>Add items using the "Atk Stock Request Items" relation manager</li>
                        <li>Specify quantities needed for each ATK item</li>
                    </ul>
                    </p>
                </div>

                <div class="step">
                    <h3>Step 4: Submit for Approval</h3>
                    <p>Once completed, save the request. The system will automatically start the approval workflow.</p>
                </div>

                <div class="tip">
                    <strong>Tip:</strong> Always verify that you have sufficient budget before requesting items, and check current stock levels to avoid over-requesting.
                </div>

                <h3 id="atk-stock-usage">ATK Stock Usage Process</h3>
                <p>To record ATK consumption:</p>

                <div class="step">
                    <h3>Step 1: Navigate to ATK Stock Usages</h3>
                    <p>Go to "Alat Tulis Kantor" → "Pengeluaran ATK".</p>
                </div>

                <div class="step">
                    <h3>Step 2: Create New Usage</h3>
                    <p>Click "Create" to start a new ATK stock usage record.</p>
                </div>

                <div class="step">
                    <h3>Step 3: Add Usage Details</h3>
                    <p>Specify:
                    <ul>
                        <li>Division the usage applies to</li>
                        <li>ATK items being consumed</li>
                        <li>Quantities for each item</li>
                        <li>Notes about the usage</li>
                    </ul>
                    </p>
                </div>

                <div class="step">
                    <h3>Step 4: Submit for Approval</h3>
                    <p>Save the usage record to start the approval process.</p>
                </div>

                <div class="tip">
                    <strong>Best Practice:</strong> Regularly record ATK stock usages to maintain accurate inventory levels and identify usage patterns that can inform future procurement decisions.
                </div>
            </div>

            <div class="section" id="marketing-media-stock-requests-usage">
                <h2>Marketing Media Stock Requests & Usage Process</h2>

                <div class="warning">
                    <strong>Important:</strong> Marketing Media inventory management is restricted to users from Marketing divisions only. If you don't see Marketing Media options in your navigation, you may not have the required permissions.
                </div>

                <h3 id="marketing-media-stock-requests">Creating Marketing Media Stock Requests</h3>
                <p>Follow these steps to request additional Marketing Media inventory for your division:</p>

                <div class="step">
                    <h3>Step 1: Navigate to Marketing Media Stock Requests</h3>
                    <p>Go to the "Marketing Media" section and click on "Permintaan Marketing Media".</p>
                </div>

                <div class="step">
                    <h3>Step 2: Create New Request</h3>
                    <p>Click the "Create" button to start a new Marketing Media stock request.</p>
                </div>

                <div class="step">
                    <h3>Step 3: Fill Request Details</h3>
                    <p>Enter the following information:
                    <ul>
                        <li>Request type (if applicable)</li>
                        <li>Notes (optional)</li>
                        <li>Add items using the "Marketing Media Stock Request Items" relation manager</li>
                        <li>Specify quantities needed for each Marketing Media item</li>
                    </ul>
                    </p>
                </div>

                <div class="step">
                    <h3>Step 4: Submit for Approval</h3>
                    <p>Once completed, save the request. The system will automatically start the approval workflow.</p>
                </div>

                <div class="tip">
                    <strong>Tip:</strong> Always verify that you have sufficient budget before requesting items, and check current stock levels to avoid over-requesting.
                </div>

                <h3 id="marketing-media-stock-usage">Marketing Media Stock Usage Process</h3>
                <p>To record Marketing Media consumption:</p>

                <div class="step">
                    <h3>Step 1: Navigate to Marketing Media Stock Usages</h3>
                    <p>Go to "Marketing Media" → "Pengeluaran Marketing Media".</p>
                </div>

                <div class="step">
                    <h3>Step 2: Create New Usage</h3>
                    <p>Click "Create" to start a new Marketing Media stock usage record.</p>
                </div>

                <div class="step">
                    <h3>Step 3: Add Usage Details</h3>
                    <p>Specify:
                    <ul>
                        <li>Division the usage applies to</li>
                        <li>Marketing Media items being consumed</li>
                        <li>Quantities for each item</li>
                        <li>Notes about the usage</li>
                    </ul>
                    </p>
                </div>

                <div class="step">
                    <h3>Step 4: Submit for Approval</h3>
                    <p>Save the usage record to start the approval process.</p>
                </div>

                <div class="tip">
                    <strong>Best Practice:</strong> Regularly record Marketing Media stock usages to maintain accurate inventory levels and identify usage patterns that can inform future procurement decisions.
                </div>
            </div>

            <div class="section" id="approvals">
                <h2>Approval Workflow</h2>
                <p>The approval system ensures proper authorization for all stock requests and usages:</p>

                <h3>Accessing the Approval Interface</h3>
                <p>Navigate to the "Approval Permintaan" section in the left menu. You'll only see requests that require your approval based on your role and division.</p>

                <h3>Approval Process</h3>
                <ol>
                    <li>Requests appear in your approval queue when they reach your step in the approval flow</li>
                    <li>Review the request details, including items requested and reasons</li>
                    <li>Either approve or reject the request</li>
                    <li>Optionally add comments to explain your decision</li>
                    <li>If approved, the request moves to the next approval step (if any)</li>
                    <li>If rejected, the request is denied and the requester is notified</li>
                </ol>

                <h3>Approval Statuses</h3>
                <ul>
                    <li><strong>Pending:</strong> Awaiting approval at this step</li>
                    <li><strong>Approved:</strong> Approved at this step</li>
                    <li><strong>Rejected:</strong> Rejected at this step</li>
                    <li><strong>Completed:</strong> All required approvals have been obtained</li>
                </ul>

                <div class="warning">
                    <strong>Caution:</strong> You can only approve requests that match your role, division, and approval step configuration. If you don't see requests that you expect to approve, verify with an Admin that your approval configuration is correct.
                </div>
            </div>

            <div class="section" id="admin">
                <h2>Administrative Functions</h2>
                <p>Administrators and Super Admins have additional system management capabilities:</p>

                <h3>Managing Item Categories</h3>
                <p>Navigate to "Settings" → "Item Inventaris" to manage ATK and Marketing Media categories and items.</p>

                <h3>Approval Flow Configuration</h3>
                <p>Admins can configure approval flows in the "Approval Flows" section (if available in your configuration).</p>

                <h3>User Management</h3>
                <p>Super Admins can manage users, roles, and permissions through the system's user management interface.</p>

                <h3>Division Management</h3>
                <p>Define and manage organizational divisions that will hold and consume inventory.</p>
            </div>

            <div class="section" id="tips">
                <h2>Helpful Tips and Best Practices</h2>

                <h3>For All Users</h3>
                <ul>
                    <li>Always check current inventory levels before requesting more items</li>
                    <li>Provide detailed notes when creating requests to expedite the approval process</li>
                    <li>Regularly check the status of your submitted requests</li>
                    <li>Log out properly when finished to maintain security</li>
                </ul>

                <h3>For Approvers</h3>
                <ul>
                    <li>Review requests promptly to avoid delays in operations</li>
                    <li>Use the commenting feature to communicate with requesters when needed</li>
                    <li>Understand your division's budget and inventory policies</li>
                    <li>Check that requested quantities are reasonable</li>
                </ul>

                <h3>For Administrators</h3>
                <ul>
                    <li>Regularly review and update approval flows as business processes change</li>
                    <li>Ensure proper division assignments for all users</li>
                    <li>Maintain accurate item categories and units of measure</li>
                    <li>Monitor system usage and generate reports as needed</li>
                </ul>

                <div class="tip">
                    <strong>Quick Navigation:</strong> Use the search function in the top navigation bar to quickly find specific items or records.
                </div>
            </div>

            <footer>
                <p>MJG ATK Management System - Tutorial Guide</p>
                <p>Last updated: October 2025</p>
            </footer>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('.nav-links a');
            const sections = document.querySelectorAll('.section');

            // Update active link based on scroll position
            function updateActiveLink() {
                let index = sections.length;

                while(--index && window.scrollY + 100 < sections[index].offsetTop) {}

                links.forEach(link => link.classList.remove('active'));
                if (index >= 0) {
                    const activeLinkId = sections[index].id;
                    document.querySelector(`.nav-links a[href="#${activeLinkId}"]`).classList.add('active');
                }
            }

            // Initial call
            updateActiveLink();

            // Add scroll event listener
            window.addEventListener('scroll', updateActiveLink);

            // Smooth scrolling for navigation links
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetSection = document.querySelector(targetId);

                    window.scrollTo({
                        top: targetSection.offsetTop - 80,
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>
