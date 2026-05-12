<?php
class User {
    public $user_id;
    public $email;
    public $password_hash;
    public $full_name;
    public $phone;
    public $address;
    public $latitude;
    public $longitude;
    public $role;
    public $current_trust_score;
    public $tier_id;
    public $wallet_balance;
    public $referral_code;
    public $referred_by_id;
    public $kyc_status;
    public $is_blacklisted;
    public $suspension_end_date;
    public $total_borrow_count;
    public $on_time_return_count;
    public $created_at;
    public $tier_name;
    public $discount_rate;
}