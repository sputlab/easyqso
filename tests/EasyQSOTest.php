<?php
// ============================================================
//  EasyQSO v2.1.2 — Test Suite (PHPUnit 10+)
//  Esegui con:  ./vendor/bin/phpunit tests/EasyQSOTest.php
//  Prerequisiti:
//    composer require --dev phpunit/phpunit
//    cp src/includes/config.example.php src/includes/config.php  # DB di test
// ============================================================
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

// Bootstrap minimo — carica le funzioni dell'app
define('TEST_MODE', true);
require_once __DIR__.'/../src/includes/config.php';
require_once __DIR__.'/../src/includes/db.php';
require_once __DIR__.'/../src/includes/auth.php';
require_once __DIR__.'/../src/includes/rate_limit.php';
require_once __DIR__.'/../src/includes/account.php';
require_once __DIR__.'/../src/includes/admin_mgmt.php';

// ── Helper: crea utente di test e lo rimuove dopo il test ───
trait UserFactory {
    private function makeUser(string $suffix='', bool $admin=false): array {
        $call  = 'IK1TEST'.strtoupper($suffix);
        $email = 'test'.strtolower($suffix).'@easyqso.test';
        $hash  = password_hash('Test1234!', PASSWORD_BCRYPT, ['cost'=>4]);
        $db    = db();
        $db->prepare('DELETE FROM eqso_users WHERE email=?')->execute([$email]);
        $db->prepare('INSERT INTO eqso_users(callsign,email,password_hash,is_verified,is_admin) VALUES(?,?,?,1,?)')
           ->execute([$call,$email,$hash,(int)$admin]);
        $id = (int)$db->lastInsertId();
        return ['id'=>$id,'callsign'=>$call,'email'=>$email,'password'=>'Test1234!','is_admin'=>(int)$admin];
    }
    private function cleanUser(int $id): void {
        db()->prepare('DELETE FROM eqso_users WHERE id=?')->execute([$id]);
    }
    private function makeQso(int $uid): int {
        $db=db();
        $db->prepare('INSERT INTO eqso_qso_log(user_id,qso_date,qso_time,callsign,band,mode) VALUES(?,?,?,?,?,?)')
           ->execute([$uid,date('Y-m-d'),date('H:i:s'),'DL1ABC','20m','SSB']);
        return (int)$db->lastInsertId();
    }
}

// ════════════════════════════════════════════════════════════
final class AuthTest extends TestCase {
    use UserFactory;

    public function test_login_success(): void {
        $u = $this->makeUser('A');
        $r = login_user($u['email'], $u['password']);
        $this->assertTrue($r['ok']);
        $this->assertArrayHasKey('token', $r);
        $this->assertEquals($u['callsign'], $r['user']['callsign']);
        $this->cleanUser($u['id']);
    }

    public function test_login_wrong_password(): void {
        $u = $this->makeUser('B');
        $r = login_user($u['email'], 'WrongPass99!');
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('Credenziali', $r['error']);
        $this->cleanUser($u['id']);
    }

    public function test_login_unverified(): void {
        $u = $this->makeUser('C');
        db()->prepare('UPDATE eqso_users SET is_verified=0 WHERE id=?')->execute([$u['id']]);
        $r = login_user($u['email'], $u['password']);
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('verifi', strtolower($r['error']));
        $this->cleanUser($u['id']);
    }

    public function test_register_duplicate_email(): void {
        $u = $this->makeUser('D');
        $r = register_user('IK1DUP', $u['email'], 'Another1!');
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('già', $r['error']);
        $this->cleanUser($u['id']);
    }

    public function test_verify_email_token(): void {
        $db    = db();
        $token = bin2hex(random_bytes(32));
        $u     = $this->makeUser('E');
        $db->prepare('UPDATE eqso_users SET is_verified=0,verify_token=? WHERE id=?')->execute([$token,$u['id']]);
        $this->assertTrue(verify_email($token));
        $this->assertFalse(verify_email($token)); // già usato
        $this->cleanUser($u['id']);
    }
}

// ════════════════════════════════════════════════════════════
final class AccountTest extends TestCase {
    use UserFactory;

    public function test_change_callsign_valid(): void {
        $u = $this->makeUser('F');
        $r = account_change_callsign($u['id'], 'IZ3XYZ');
        $this->assertTrue($r['ok']);
        $this->assertEquals('IZ3XYZ', $r['callsign']);
        $this->cleanUser($u['id']);
    }

    public function test_change_callsign_invalid_format(): void {
        $u = $this->makeUser('G');
        $r = account_change_callsign($u['id'], 'TOOLONGCALLSIGN123');
        $this->assertFalse($r['ok']);
        $this->cleanUser($u['id']);
    }

    public function test_change_password_success(): void {
        $u = $this->makeUser('H');
        $r = account_change_password($u['id'], 'Test1234!', 'NewPass99!');
        $this->assertTrue($r['ok']);
        // Verifica che il login funzioni con la nuova password
        $l = login_user($u['email'], 'NewPass99!');
        $this->assertTrue($l['ok']);
        $this->cleanUser($u['id']);
    }

    public function test_change_password_wrong_old(): void {
        $u = $this->makeUser('I');
        $r = account_change_password($u['id'], 'WrongOld99!', 'NewPass99!');
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('errata', strtolower($r['error']));
        $this->cleanUser($u['id']);
    }

    public function test_change_password_weak(): void {
        $u = $this->makeUser('J');
        $r = account_change_password($u['id'], 'Test1234!', 'short');
        $this->assertFalse($r['ok']);
        $this->cleanUser($u['id']);
    }

    public function test_change_password_no_uppercase(): void {
        $u = $this->makeUser('K');
        $r = account_change_password($u['id'], 'Test1234!', 'nouppercase1!');
        $this->assertFalse($r['ok']);
        $this->cleanUser($u['id']);
    }

    public function test_request_delete_invalid_action(): void {
        $u = $this->makeUser('L');
        $r = account_request_delete($u['id'], 'invalid');
        $this->assertFalse($r['ok']);
        $this->cleanUser($u['id']);
    }

    public function test_confirm_delete_with_delete_qso(): void {
        $u   = $this->makeUser('M');
        $qid = $this->makeQso($u['id']);
        // Crea token manualmente
        $db    = db();
        $token = bin2hex(random_bytes(32));
        $exp   = date('Y-m-d H:i:s', time()+3600);
        $db->prepare('INSERT INTO eqso_account_tokens(user_id,token,action,payload,expires_at)VALUES(?,?,?,?,?)')
           ->execute([$u['id'],$token,'delete_account',json_encode(['qso_action'=>'delete']),$exp]);
        $r = account_confirm_delete($token);
        $this->assertTrue($r['ok']);
        // Utente e QSO eliminati
        $st=$db->prepare('SELECT id FROM eqso_users WHERE id=?');$st->execute([$u['id']]);
        $this->assertFalse($st->fetch());
        $st=$db->prepare('SELECT id FROM eqso_qso_log WHERE id=?');$st->execute([$qid]);
        $this->assertFalse($st->fetch());
    }

    public function test_confirm_delete_with_anonymize_qso(): void {
        $u   = $this->makeUser('N');
        $qid = $this->makeQso($u['id']);
        $db    = db();
        $token = bin2hex(random_bytes(32));
        $exp   = date('Y-m-d H:i:s', time()+3600);
        $db->prepare('INSERT INTO eqso_account_tokens(user_id,token,action,payload,expires_at)VALUES(?,?,?,?,?)')
           ->execute([$u['id'],$token,'delete_account',json_encode(['qso_action'=>'anonymize']),$exp]);
        $r = account_confirm_delete($token);
        $this->assertTrue($r['ok']);
        // QSO anonimizzato
        $st=$db->prepare('SELECT callsign FROM eqso_qso_log WHERE id=?');$st->execute([$qid]);
        $row=$st->fetch();
        $this->assertNotFalse($row);
        $this->assertEquals('DELETED', $row['callsign']);
    }

    public function test_confirm_delete_expired_token(): void {
        $u   = $this->makeUser('O');
        $db  = db();
        $token = bin2hex(random_bytes(32));
        $exp = date('Y-m-d H:i:s', time()-1); // scaduto
        $db->prepare('INSERT INTO eqso_account_tokens(user_id,token,action,payload,expires_at)VALUES(?,?,?,?,?)')
           ->execute([$u['id'],$token,'delete_account',json_encode(['qso_action'=>'anonymize']),$exp]);
        $r = account_confirm_delete($token);
        $this->assertFalse($r['ok']);
        $this->cleanUser($u['id']);
    }
}

// ════════════════════════════════════════════════════════════
final class AdminTest extends TestCase {
    use UserFactory;

    public function test_list_users_returns_paginated(): void {
        $u = $this->makeUser('P');
        $r = admin_list_users('', 'all', 1, 50);
        $this->assertArrayHasKey('users', $r);
        $this->assertArrayHasKey('total', $r);
        $this->assertGreaterThan(0, $r['total']);
        $this->cleanUser($u['id']);
    }

    public function test_list_users_search(): void {
        $u = $this->makeUser('Q');
        $r = admin_list_users('IK1TESTQ', 'all', 1);
        $this->assertEquals(1, count(array_filter($r['users'], fn($x)=>$x['callsign']==='IK1TESTQ')));
        $this->cleanUser($u['id']);
    }

    public function test_update_user_toggle_active(): void {
        $admin = $this->makeUser('R', true);
        $user  = $this->makeUser('S', false);
        $r = admin_update_user($admin['id'], $admin['callsign'], $user['id'], ['is_active'=>0,'suspended_reason'=>'Test']);
        $this->assertTrue($r['ok']);
        $detail = admin_get_user_detail($user['id']);
        $this->assertEquals(0, (int)$detail['is_active']);
        $this->cleanUser($admin['id']);
        $this->cleanUser($user['id']);
    }

    public function test_cannot_remove_last_admin(): void {
        // Conta admin esistenti
        $cnt = (int)db()->query('SELECT COUNT(*) FROM eqso_users WHERE is_admin=1')->fetchColumn();
        if ($cnt > 1) { $this->markTestSkipped('Più admin presenti, skip.'); }
        $admin = $this->makeUser('T', true);
        // Prova a rimuovere privilegi admin all'unico admin rimasto
        // (si deve impedire se è l'unico)
        $r = admin_update_user($admin['id'], $admin['callsign'], $admin['id'], ['is_admin'=>0]);
        // Deve fallire (auto-rimozione) o perché è l'unico admin
        $this->assertFalse($r['ok']);
        $this->cleanUser($admin['id']);
    }

    public function test_create_user_by_admin(): void {
        $admin = $this->makeUser('U', true);
        $r = admin_create_user($admin['id'], $admin['callsign'], [
            'callsign'=>'IK1NEW','email'=>'new@easyqso.test','password'=>'NewUser1!','is_admin'=>false
        ]);
        $this->assertTrue($r['ok']);
        $this->assertArrayHasKey('id', $r);
        db()->prepare('DELETE FROM eqso_users WHERE id=?')->execute([$r['id']]);
        $this->cleanUser($admin['id']);
    }

    public function test_delete_user_with_qso_delete(): void {
        $admin = $this->makeUser('V', true);
        $user  = $this->makeUser('W', false);
        $qid   = $this->makeQso($user['id']);
        $r = admin_delete_user($admin['id'], $admin['callsign'], $user['id'], 'delete');
        $this->assertTrue($r['ok']);
        $st=db()->prepare('SELECT id FROM eqso_qso_log WHERE id=?');$st->execute([$qid]);
        $this->assertFalse($st->fetch());
        $this->cleanUser($admin['id']);
    }

    public function test_admin_cannot_delete_self(): void {
        $admin = $this->makeUser('X', true);
        $r = admin_delete_user($admin['id'], $admin['callsign'], $admin['id'], 'anonymize');
        $this->assertFalse($r['ok']);
        $this->cleanUser($admin['id']);
    }

    public function test_audit_log_written_on_update(): void {
        $admin = $this->makeUser('Y', true);
        $user  = $this->makeUser('Z', false);
        $before = (int)db()->query('SELECT COUNT(*) FROM eqso_audit_log')->fetchColumn();
        admin_update_user($admin['id'], $admin['callsign'], $user['id'], ['is_verified'=>1]);
        $after = (int)db()->query('SELECT COUNT(*) FROM eqso_audit_log')->fetchColumn();
        $this->assertEquals($before+1, $after);
        $this->cleanUser($admin['id']);
        $this->cleanUser($user['id']);
    }
}

// ════════════════════════════════════════════════════════════
final class QsoIntegrationTest extends TestCase {
    use UserFactory;

    /** Il salvataggio QSO non deve essere rotto dalle nuove funzioni */
    public function test_qso_save_still_works(): void {
        $u  = $this->makeUser('QSO');
        $db = db();
        $db->prepare('INSERT INTO eqso_qso_log(user_id,qso_date,qso_time,callsign,band,mode,rst_sent,rst_rcvd)VALUES(?,?,?,?,?,?,?,?)')
           ->execute([$u['id'],date('Y-m-d'),date('H:i:s'),'IW2TEST','20m','FT8','59','57']);
        $st = $db->prepare('SELECT * FROM eqso_qso_log WHERE user_id=?');
        $st->execute([$u['id']]);
        $rows = $st->fetchAll();
        $this->assertCount(1, $rows);
        $this->assertEquals('IW2TEST', $rows[0]['callsign']);
        $this->assertEquals('20m', $rows[0]['band']);
        $this->assertEquals('FT8', $rows[0]['mode']);
        $this->cleanUser($u['id']); // CASCADE elimina anche i QSO
    }

    public function test_qso_persists_after_callsign_change(): void {
        $u   = $this->makeUser('QSO2');
        $qid = $this->makeQso($u['id']);
        account_change_callsign($u['id'], 'IZ9NEW');
        $st  = db()->prepare('SELECT id FROM eqso_qso_log WHERE id=? AND user_id=?');
        $st->execute([$qid,$u['id']]);
        $this->assertNotFalse($st->fetch()); // QSO ancora presente
        $this->cleanUser($u['id']);
    }

    public function test_qso_persists_after_password_change(): void {
        $u   = $this->makeUser('QSO3');
        $qid = $this->makeQso($u['id']);
        account_change_password($u['id'], 'Test1234!', 'NewPwd99!');
        $st  = db()->prepare('SELECT id FROM eqso_qso_log WHERE id=?');
        $st->execute([$qid]);
        $this->assertNotFalse($st->fetch());
        $this->cleanUser($u['id']);
    }
}

// ════════════════════════════════════════════════════════════
final class RateLimitTest extends TestCase {
    public function test_rate_limit_allows_within_window(): void {
        $id = 'test-rate-'.uniqid();
        db()->prepare('DELETE FROM eqso_rate_limits WHERE identifier=?')->execute([$id]);
        for ($i=0; $i<5; $i++) $this->assertTrue(rate_check($id,'login',5,900));
        db()->prepare('DELETE FROM eqso_rate_limits WHERE identifier=?')->execute([$id]);
    }

    public function test_rate_limit_blocks_over_limit(): void {
        $id = 'test-rate-block-'.uniqid();
        db()->prepare('DELETE FROM eqso_rate_limits WHERE identifier=?')->execute([$id]);
        for ($i=0; $i<5; $i++) rate_check($id,'login',5,900);
        $this->assertFalse(rate_check($id,'login',5,900));
        db()->prepare('DELETE FROM eqso_rate_limits WHERE identifier=?')->execute([$id]);
    }

    public function test_rate_reset_clears_counter(): void {
        $id = 'test-rate-reset-'.uniqid();
        db()->prepare('DELETE FROM eqso_rate_limits WHERE identifier=?')->execute([$id]);
        for ($i=0; $i<5; $i++) rate_check($id,'login',5,900);
        $this->assertFalse(rate_check($id,'login',5,900));
        rate_reset($id,'login');
        $this->assertTrue(rate_check($id,'login',5,900));
        db()->prepare('DELETE FROM eqso_rate_limits WHERE identifier=?')->execute([$id]);
    }
}
