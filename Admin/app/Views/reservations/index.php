<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reservations</h1>
    <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#reservationModal" onclick="resetReservationForm()" aria-label="Add New Reservation">
        <i class="fas fa-plus me-2" aria-hidden="true"></i> Add Reservation
    </button>
</div>

<div class="table-responsive">
    <table class="table table-custom table-hover">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Phone</th>
                <th>Date</th>
                <th>Time</th>
                <th>Party Size</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data['reservations'])): ?>
                <?php foreach ($data['reservations'] as $res): ?>
                <tr>
                    <td><?= htmlspecialchars($res['customer_name']) ?></td>
                    <td><?= htmlspecialchars($res['contact_phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($res['reservation_date']) ?></td>
                    <td><?= htmlspecialchars($res['reservation_time']) ?></td>
                    <td><?= htmlspecialchars($res['party_size']) ?></td>
                    <td>
                        <!-- Edit Button and Delete Link -->
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-sm btn-outline-gold me-2" 
                                    data-id="<?= htmlspecialchars($res['reservation_id'] ?? $res['id']) ?>"
                                    data-name="<?= htmlspecialchars($res['customer_name']) ?>"
                                    data-phone="<?= htmlspecialchars($res['contact_phone'] ?? '') ?>"
                                    data-date="<?= htmlspecialchars($res['reservation_date']) ?>"
                                    data-time="<?= htmlspecialchars($res['reservation_time']) ?>"
                                    data-size="<?= htmlspecialchars($res['party_size']) ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#reservationModal"
                                    onclick="editReservation(this)"
                                    aria-label="Edit Reservation for <?= htmlspecialchars($res['customer_name']) ?>">
                                <i class="fas fa-edit" aria-hidden="true"></i> Edit
                            </button>
                            
                            <a href="index.php?page=reservations&action=delete&id=<?= $res['reservation_id'] ?? $res['id'] ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Are you sure you want to delete this reservation?');"
                               aria-label="Delete Reservation for <?= htmlspecialchars($res['customer_name']) ?>">
                                <i class="fas fa-trash" aria-hidden="true"></i> Delete
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No reservations found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Reservation Modal -->
<div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reservationModalLabel">New Reservation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- FORM ACTION: save (Controller handles Update vs Create) -->
            <form id="reservationForm" action="index.php?page=reservations&action=save" method="POST">
                <div class="modal-body">
                    <!-- HIDDEN FIELD: reservation_id -->
                    <input type="hidden" id="reservationId" name="reservation_id">

                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label">Phone Number</label>
                        <!-- MATCH: name="contact_phone" -->
                        <input type="text" class="form-control" id="contact_phone" name="contact_phone" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="reservation_date" class="form-label">Date</label>
                            <!-- MATCH: name="reservation_date" -->
                            <input type="date" class="form-control" id="reservation_date" name="reservation_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reservation_time" class="form-label">Time</label>
                            <!-- MATCH: name="reservation_time" -->
                            <input type="time" class="form-control" id="reservation_time" name="reservation_time" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="party_size" class="form-label">Party Size</label>
                            <!-- MATCH: name="party_size" -->
                            <input type="number" class="form-control" id="party_size" name="party_size" min="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-gold">Save Reservation</button>
                </div>
            </form>
        </div>
    </div>
</div>
