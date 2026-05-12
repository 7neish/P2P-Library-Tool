
<?php
require_once __DIR__ . '/../../includes/DBController.php';
require_once __DIR__ . '/../../models/Review.php';

class ReviewController {
    public function create(Review $rev) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $bid  = (int)$rev->booking_id;
        $rid  = (int)$rev->reviewer_id;
        $rate = (int)$rev->rating;
        $cmt  = $db->escape($rev->comment ?? '');
        $cond = (int)($rev->tool_condition_rating ?? $rate);
        return mysqli_query($conn, "INSERT INTO reviews (booking_id, reviewer_id, rating, comment, tool_condition_rating)
                                    VALUES ($bid, $rid, $rate, '$cmt', $cond)");
    }

    public function findByBooking($bookingId) {
        $db = DBController::getInstance();
        $conn = $db->getConnection();
        $id = (int)$bookingId;
        $res = mysqli_query($conn, "SELECT r.*, u.full_name FROM reviews r
                                     JOIN users u ON r.reviewer_id=u.user_id
                                     WHERE booking_id=$id");
        $reviews = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $review = new Review();
            foreach ($row as $k => $v) {
                if (property_exists($review, $k)) $review->$k = $v;
            }
            $reviews[] = $review;
        }
        return $reviews;
    }
}