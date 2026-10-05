<?php

namespace Tests\Unit;

use App\Services\QrisService;
use Tests\TestCase;

class H2HTest extends TestCase
{
    public function test_qris_static_to_dynamic_conversion(): void
    {
        $qrisService = new QrisService();
        $staticQris = '00020101021126590014ID.LINKAJA.WWW0118936009110021200388021000012345670303UMI51440014ID.CO.QRIS.WWW0215ID10200212003880303UMI5204581253033605802ID5913WEB TOKO H2H6007JAKARTA61051234062070703A016304';
        
        $dynamicQris = $qrisService->convertStaticToDynamic($staticQris, 50123);
        
        $this->assertNotEmpty($dynamicQris);
        $this->assertStringContainsString('010212', $dynamicQris); // Dynamic Point of Initiation
        $this->assertStringContainsString('540550123', $dynamicQris); // Amount Tag 54 (length 05, value 50123)
        $this->assertStringContainsString('5802ID', $dynamicQris);
        $this->assertStringContainsString('6304', $dynamicQris);
        $this->assertEquals(4, strlen(substr($dynamicQris, -4))); // 4-char CRC16
    }
}
