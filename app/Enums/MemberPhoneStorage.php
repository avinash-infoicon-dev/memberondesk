<?php

namespace App\Enums;

enum MemberPhoneStorage: string
{
    /** Phone stays on the Android device only; DB keeps other member fields. */
    case DeviceLocal = 'device_local';

    /**
     * Phone is encrypted on-device (K1); ciphertext + K1 wrapped with recovery
     * key K2 are stored on the server for recovery if the phone is lost.
     */
    case ServerEncrypted = 'server_encrypted';

    public function label(): string
    {
        return match ($this) {
            self::DeviceLocal => 'Store mobile on device only (privacy)',
            self::ServerEncrypted => 'Store encrypted mobile on server (recoverable)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DeviceLocal => 'Member phone numbers are kept only on the owner’s Android app. The server stores name, plans, attendance, etc., without the plain mobile number.',
            self::ServerEncrypted => 'The app encrypts the mobile with a device key (K1). The server stores ciphertext and K1 wrapped by a recovery key (K2), so numbers can be restored if the phone is lost.',
        };
    }
}
