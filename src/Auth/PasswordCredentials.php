<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

/**
 * What the password and MFA grants send. The MFA passcode is the one-time code from the user's
 * authenticator app; without it the plain password grant is used.
 */
final class PasswordCredentials
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $username,
        public readonly string $password,
        public readonly ?string $mfaPasscode = null,
        public readonly string $mfaVendor = 'google',
        public readonly string $mfaType = 'authenticator',
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function grant(): array
    {
        $grant = [
            'grant_type'    => $this->mfaPasscode === null ? 'password' : 'mfa',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username'      => $this->username,
            'password'      => $this->password,
        ];

        if ($this->mfaPasscode !== null) {
            $grant += ['mfa_vendor' => $this->mfaVendor, 'mfa_type' => $this->mfaType, 'passcode' => $this->mfaPasscode];
        }

        return $grant;
    }

    /**
     * Identifies this login in a token store without putting the password in the key.
     */
    public function storeKey(string $scope): string
    {
        return hash('sha256', $scope . "\0" . $this->clientId . "\0" . $this->username);
    }
}
