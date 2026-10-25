<?php

namespace Tests\Feature;

use App\Http\Controllers\SiteController;
use App\Mail\WebsiteEnquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class WebsiteEnquiryMailTest extends TestCase
{
    private function lead(): array
    {
        return ['type'=>'1', 'full_name'=>'Example Contact', 'email_id'=>'contact@example.com',
            'contact_no'=>'+1 202 555 0123', 'company'=>'Example Company', 'role'=>'Research Manager',
            'country'=>'India', 'message'=>"Please send a sample.\n<script>alert('unsafe')</script>", 'created_at'=>now()];
    }

    public function test_rendered_mail_has_report_details_logo_and_escaped_customer_content(): void
    {
        $mail = new WebsiteEnquiry($this->lead(), (object) ['title'=>'Example Market Report', 'slug'=>'example-market'], '192.0.2.1', 42);
        $html = $mail->render();
        $this->assertStringContainsString('Example Market Report', $html);
        $this->assertStringContainsString('Report Name', $html);
        $this->assertStringContainsString('192.0.2.1', $html);
        $this->assertStringContainsString('Eternity Insights', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('/authuser/manage/enquiries/42/edit', $html);
        $this->assertSame('contact@example.com', $mail->replyTo[0]['address']);
    }

    public function test_contact_submission_sends_branded_admin_mail_with_hidden_copy_recipients(): void
    {
        Mail::fake();
        config(['site.recaptcha.enabled'=>false, 'site.mail_notifications'=>true,
            'site.admin_email'=>'sales@example.com', 'site.admin_bcc'=>'manager@example.com, SALES@example.com, manager@example.com']);
        $query = Mockery::mock(\Illuminate\Database\Query\Builder::class);
        $query->shouldReceive('insertGetId')->once()->andReturn(42);
        DB::shouldReceive('table')->once()->with('reports_enquiries')->andReturn($query);
        $data = $this->lead(); unset($data['type'], $data['created_at']);
        $this->post('/contactus', $data)->assertRedirect('/contactus/thankyou');
        Mail::assertSent(WebsiteEnquiry::class, function ($mail) {
            return $mail->hasTo('sales@example.com') && $mail->hasBcc('manager@example.com')
                && !$mail->hasBcc('sales@example.com') && $mail->enquiryId === 42
                && $mail->lead['type'] === '5';
        });
        Mail::assertSentCount(1);
    }

    public function test_customer_and_password_notifications_do_not_receive_admin_bcc(): void
    {
        config(['site.mail_notifications'=>true, 'site.admin_bcc'=>'manager@example.com']);
        Mail::shouldReceive('raw')->once()->withArgs(function ($body, $callback) {
            $message = Mockery::mock(\Illuminate\Mail\Message::class);
            $message->shouldReceive('to')->once()->with('customer@example.com')->andReturnSelf();
            $message->shouldReceive('subject')->once()->with('Password reset')->andReturnSelf();
            $message->shouldReceive('bcc')->never();
            $callback($message);
            return true;
        })->andReturnNull();
        SiteController::notify('customer@example.com', 'Password reset', 'Reset link');
    }

    public function test_admin_checkout_notifications_receive_bcc(): void
    {
        config(['site.mail_notifications'=>true, 'site.admin_bcc'=>'manager@example.com', 'site.admin_email'=>'sales@example.com']);
        Mail::shouldReceive('raw')->once()->withArgs(function ($body, $callback) {
            $message = Mockery::mock(\Illuminate\Mail\Message::class);
            $message->shouldReceive('to')->once()->with('sales@example.com')->andReturnSelf();
            $message->shouldReceive('subject')->once()->with('New checkout attempt')->andReturnSelf();
            $message->shouldReceive('bcc')->once()->with(['manager@example.com'])->andReturnSelf();
            $callback($message);
            return true;
        })->andReturnNull();
        SiteController::notify('sales@example.com', 'New checkout attempt', 'Order information', true);
    }
}
