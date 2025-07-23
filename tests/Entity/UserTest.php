<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testTheAutomaticApiTokenSettingWhenAnUserIsCreated(): void
    {
        $user = new User();
        $this->assertNull($user->getApiToken());
    }

    public function testThanAnUserHasAtleastOneRoleUser(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

}
