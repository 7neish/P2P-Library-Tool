<?php
require_once __DIR__ . '/BookingController.php';
require_once __DIR__ . '/../tools/ToolController.php';

class BookingActionController {
    public function confirm($bookingId) {
        $bc = new BookingController();
        return $bc->updateStatus($bookingId, 'CONFIRMED');
    }
    public function activate($bookingId) {
        $bc = new BookingController();
        $booking = $bc->findById($bookingId);
        if ($booking && isset($booking->tool_id)) {
            $tc = new ToolController();
            $tc->updateStatus($booking->tool_id, 'BORROWED');
            return $bc->updateStatus($bookingId, 'ACTIVE');
        }
        return false;
    }
    public function markReturned($bookingId) {
        $bc = new BookingController();
        $booking = $bc->findById($bookingId);
        if ($booking && isset($booking->tool_id)) {
            $tc = new ToolController();
            $tc->updateStatus($booking->tool_id, 'AVAILABLE');
            return $bc->markReturned($bookingId);
        }
        return false;
    }
    public function cancel($bookingId) {
        $bc = new BookingController();
        return $bc->updateStatus($bookingId, 'CANCELLED');
    }
}