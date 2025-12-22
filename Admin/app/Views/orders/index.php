<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Orders Management</h1>
</div>

<div class="table-responsive">
    <table class="table table-custom table-hover">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Table</th>
                <th>Waiter</th>
                <th>Total ($)</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data['orders'])): ?>
                <?php foreach ($data['orders'] as $order): ?>
                <tr>
                    <td>#<?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($order['table_number'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($order['waiter_name'] ?? 'Unknown') ?></td>
                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                    <td>
                        <?php 
                            $statusClass = 'bg-secondary';
                            // Gold accent for 'new'
                            if ($order['status'] === 'new') $statusClass = 'bg-gold text-dark border border-warning'; 
                            elseif ($order['status'] === 'preparing') $statusClass = 'bg-info text-dark';
                            elseif ($order['status'] === 'ready') $statusClass = 'bg-primary';
                            elseif ($order['status'] === 'completed') $statusClass = 'bg-success';
                            elseif ($order['status'] === 'cancelled') $statusClass = 'bg-danger';
                        ?>
                        <span class="badge <?= $statusClass ?>" style="<?= $order['status'] === 'new' ? 'background-color: #c9b078 !important;' : '' ?>">
                            <?= ucfirst(htmlspecialchars($order['status'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($order['created_at'] ?? $order['order_date'] ?? 'N/A') ?></td>
                    <td>
                        <!-- Update Status Dropdown -->
                        <form action="/Admin/public/index.php?page=orders&action=updateStatus" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-gold dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    Update Status
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li><button class="dropdown-item" type="submit" name="status" value="new">New</button></li>
                                    
                                    <?php 
                                    // RBAC: Hide "Preparing" and "Ready" for Waiter (Role 1)
                                    if(isset($_SESSION['role_id']) && $_SESSION['role_id'] > 1): 
                                    ?>
                                    <li><button class="dropdown-item" type="submit" name="status" value="preparing">Preparing</button></li>
                                    <li><button class="dropdown-item" type="submit" name="status" value="ready">Ready</button></li>
                                    <?php endif; ?>
                                    
                                    <li><button class="dropdown-item" type="submit" name="status" value="completed">Completed</button></li>
                                    <li><button class="dropdown-item text-danger" type="submit" name="status" value="cancelled">Cancelled</button></li>
                                </ul>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No orders found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
