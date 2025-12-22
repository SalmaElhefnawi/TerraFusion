<?php
session_start();
require_once 'config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        // User not found in DB
        session_destroy();
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - TerraFusion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/userprofile.css">
</head>
<body>
  <main id="main" class="main">
    <section id="user-profile" class="user-profile section">
      <div class="container">
        <div class="section-title">
          <h2>My Account</h2>
          <p class="text-muted">Manage your profile and orders</p>
        </div>

        <!-- Profile Hero Section -->
        <div class="profile-hero text-center mb-5">
          <div class="hero-content">
            <div class="avatar-circle mb-3">
              <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <h2 class="mb-1 text-white"><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="text-white-50 mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
              <i class="bi bi-pencil-square me-2"></i> Edit Profile
            </button>
          </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="row mb-5">
            <?php
            // Calculate Stats (re-using logic or moving it here)
            $activeCount = 0;
            $completedCount = 0;
            $totalSpent = 0;

            // Fetch all orders for stats calculation
            $stmtAllOrders = $pdo->prepare("SELECT * FROM orders WHERE served_by_fk = ? AND status != 'New'");
            $stmtAllOrders->execute([$_SESSION['user_id']]);
            $allOrders = $stmtAllOrders->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allOrders as $o) {
                $s = strtolower($o['status']);
                if (in_array($s, ['delivered', 'completed', 'cancelled'])) {
                    $completedCount++;
                } else {
                    $activeCount++;
                }
                
                // User requested to calculate ALL spent money (excluding cancelled)
                if ($s != 'cancelled') {
                    $totalSpent += $o['total_amount'];
                }
            }
            ?>
          <div class="col-md-4 mb-4">
            <div class="card stat-card bg-dark-gradient">
              <div class="card-body text-center p-4">
                <i class="bi bi-basket3 display-4 text-primary mb-3"></i>
                <h6 class="text-white-50 text-uppercase ls-1">Active Orders</h6>
                <h2 class="text-white mb-0"><?php echo $activeCount; ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card stat-card bg-dark-gradient">
              <div class="card-body text-center p-4">
                <i class="bi bi-check-circle display-4 text-success mb-3"></i>
                <h6 class="text-white-50 text-uppercase ls-1">Completed Orders</h6>
                <h2 class="text-white mb-0"><?php echo $completedCount; ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card stat-card bg-dark-gradient">
              <div class="card-body text-center p-4">
                <i class="bi bi-wallet2 display-4 text-warning mb-3"></i>
                <h6 class="text-white-50 text-uppercase ls-1">Total Spent</h6>
                <h2 class="text-white mb-0"><?php echo number_format($totalSpent, 2); ?> <small class="fs-6">EGP</small></h2>
              </div>
            </div>
          </div>
        </div>

        <!-- Orders Section -->
        <div class="row">
          <div class="col-12">
            <div class="card shadow-lg border-0 bg-glass">
              <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                <h4 class="text-white mb-0">Order History</h4>
              </div>
              <div class="card-body p-4">
                <div class="table-responsive">
                  <table class="table table-hover align-middle custom-table">
                    <thead>
                      <tr>
                        <th scope="col" class="text-uppercase text-gold">Order ID</th>
                        <th scope="col" class="text-uppercase text-gold">Date</th>
                        <th scope="col" class="text-uppercase text-gold">Total</th>
                        <th scope="col" class="text-uppercase text-gold">Status</th>
                        <th scope="col" class="text-uppercase text-gold text-end">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($allOrders) > 0): ?>
                        <?php 
                        // Sort orders by date DESC for display
                        usort($allOrders, function($a, $b) {
                            return strtotime($b['order_date']) - strtotime($a['order_date']);
                        });
                        
                        foreach ($allOrders as $order): 
                            $statusClass = '';
                            switch (strtolower($order['status'])) {
                                case 'pending': $statusClass = 'badge bg-warning text-dark'; break;
                                case 'processing': 
                                case 'in_preparation': $statusClass = 'badge bg-info text-dark'; break;
                                case 'ready': 
                                case 'out_for_delivery': $statusClass = 'badge bg-primary'; break;
                                case 'delivered': 
                                case 'completed': $statusClass = 'badge bg-success'; break;
                                case 'cancelled': $statusClass = 'badge bg-danger'; break;
                                default: $statusClass = 'badge bg-secondary';
                            }
                        ?>
                        <tr>
                          <td class="fw-bold text-white">#<?php echo $order['order_id']; ?></td>
                          <td class="text-white-50"><?php echo date('M d, Y', strtotime($order['order_date'])); ?> <small class="d-block"><?php echo date('h:i A', strtotime($order['order_date'])); ?></small></td>
                          <td class="fw-bold text-white"><?php echo number_format($order['total_amount'], 2); ?> EGP</td>
                          <td><span class="<?php echo $statusClass; ?> px-3 py-2 rounded-pill"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                          <td class="text-end">
                            <a href="order-tracking.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-gold">Track Order</a>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-cart-x display-4 d-block mb-3"></i>
                            No orders found. <a href="menu.php" class="text-primary">Start ordering now!</a>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark-modal">
          <div class="modal-header border-bottom-0">
            <h5 class="modal-title text-white">Edit Profile</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
             <ul class="nav nav-tabs nav-tabs-bordered mb-4" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">Details</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">Change Password</button>
                </li>
             </ul>
             
             <div class="tab-content">
                <div class="tab-pane fade show active" id="details" role="tabpanel">
                    <form id="profileForm">
                      <div class="mb-3">
                        <label class="form-label text-white-50">Full Name</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" id="fullName" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                      </div>
                      <div class="mb-3">
                        <label class="form-label text-white-50">Email</label>
                        <input type="email" class="form-control bg-dark text-white border-secondary" id="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                      </div>
                      <div class="mb-3">
                        <label class="form-label text-white-50">Phone</label>
                        <input type="tel" class="form-control bg-dark text-white border-secondary" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                      </div>
                      <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                    </form>
                </div>
                
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <form id="passwordForm">
                      <div class="mb-3">
                        <label class="form-label text-white-50">Current Password</label>
                        <input type="password" class="form-control bg-dark text-white border-secondary" id="currentPassword">
                      </div>
                      <div class="mb-3">
                        <label class="form-label text-white-50">New Password</label>
                        <input type="password" class="form-control bg-dark text-white border-secondary" id="newPassword">
                      </div>
                      <div class="mb-3">
                        <label class="form-label text-white-50">Confirm New Password</label>
                        <input type="password" class="form-control bg-dark text-white border-secondary" id="confirmPassword">
                      </div>
                      <button type="submit" class="btn btn-primary w-100">Update Password</button>
                    </form>
                </div>
             </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/userprofile.js"></script>
</body>
</html>
