<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Opens every page with the demo data and checks it renders (HTTP 200). Run: php artisan test --filter=PagesRenderTest */
class PagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        $this->seed();
        foreach (['/', '/login', '/register', '/admin/login', '/forgot-password', '/reset-password/sometoken?email=a@b.test'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_every_admin_page_renders(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@demo.test')->first();

        $urls = [
            '/admin', '/admin/equipment', '/admin/equipment/create', '/admin/equipment/1', '/admin/equipment/1/edit',
            '/admin/categories', '/admin/borrowers', '/admin/borrowers/2', '/admin/borrowers/2/edit',
            '/admin/requests', '/admin/requests/1', '/admin/returns', '/admin/returns/1',
            '/admin/history', '/admin/reports', '/admin/reports?type=inventory', '/admin/reports?type=overdue',
            '/admin/users', '/admin/users/create', '/admin/users/2/edit', '/admin/settings', '/profile',
        ];
        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_every_borrower_page_renders(): void
    {
        $this->seed();
        $borrower = User::where('email', 'borrower@demo.test')->first();

        $urls = [
            '/borrower', '/borrower/equipment', '/borrower/equipment/1', '/borrower/requests',
            '/borrower/requests/create', '/borrower/requests/create?equipment=1', '/borrower/requests/1', '/borrower/history', '/profile',
        ];
        foreach ($urls as $url) {
            $this->actingAs($borrower)->get($url)->assertOk();
        }
    }

    public function test_notification_bell_shows_real_items(): void
    {
        $this->seed();

        // Admin: the demo data has pending requests and an overdue one
        $admin = User::where('email', 'admin@demo.test')->first();
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('notif-badge')->assertSee('sent a request');

        // Ana has an approved request in the demo data
        $ana = User::where('email', 'ana@demo.test')->first();
        $this->actingAs($ana)->get('/borrower')->assertOk()->assertSee('Request approved');
    }

    public function test_back_to_home_button_on_login_and_register_points_to_the_landing_page(): void
    {
        foreach (['/login', '/register'] as $url) {
            $this->get($url)->assertOk()->assertSee('Back to Home')->assertSee('href="' . route('home') . '"', false);
        }

        // ...and that link really is the landing page
        $this->get(route('home'))->assertOk()->assertSee('Borrow Smarter. Manage Better.');
    }

    public function test_register_form_uses_the_new_full_name_placeholder(): void
    {
        $this->get('/register')->assertOk()
            ->assertSee('placeholder="Enter your full name"', false)
            ->assertDontSee('Maria Santos');
    }

    public function test_the_three_auth_pages_have_their_key_elements_and_working_links(): void
    {
        // Borrower login
        $this->get('/login')->assertOk()->assertSee('Welcome back')->assertSee('Remember me')->assertSee('Forgot password?')
            ->assertSee('Create an account')->assertSee('Administrator sign in')
            ->assertSee('href="' . route('admin.login') . '"', false)->assertSee('href="' . route('password.request') . '"', false);

        // Registration
        $this->get('/register')->assertOk()->assertSee('Create your account')->assertSee('Enter your full name', false)
            ->assertDontSee('Maria Santos')->assertSee('href="' . route('login') . '"', false);

        // Administrator sign in
        $this->get('/admin/login')->assertOk()->assertSee('Administrator sign in')->assertSee('Restricted area. Authorized staff only.')
            ->assertSee('Keep me signed in')->assertSee('Sign in to admin panel')->assertSee('Back to website')->assertSee('Borrower login')
            ->assertSee('action="' . route('admin.login.store') . '"', false)->assertSee('href="' . route('home') . '"', false);
    }

    public function test_futuristic_auth_layouts_show_role_branding_and_background(): void
    {
        $this->get('/login')->assertOk()->assertSee('BORROWER ACCESS')->assertSee('Borrow equipment.')->assertSee('Get more done.')
            ->assertSee('Welcome back!')->assertSee('class="tech-bg"', false)->assertSee('Back to Home');

        $this->get('/admin/login')->assertOk()->assertSee('ADMINISTRATOR ACCESS')->assertSee('Access the admin panel securely.')
            ->assertSee('class="tech-bg"', false)->assertSee('Back to Home');

        $this->get('/register')->assertOk()->assertSee('Join EquipTrack and start borrowing equipment today.')->assertSee('Already have an account?')
            ->assertSee('Log in instead')->assertSee('Secure sign-up with instant account access')
            ->assertSee('placeholder="Enter your full name"', false)->assertDontSee('Maria Santos');

        // the other pages that share the centered layout still render with Back to Home
        $this->get('/forgot-password')->assertOk()->assertSee('Back to Home');
    }

    public function test_landing_page_has_every_section_with_working_links(): void
    {
        $this->seed();
        $html = $this->get('/')->assertOk();

        // hero
        $html->assertSee('Borrow Smarter. Manage Better.')   // page title
            ->assertSee('Smarter.')->assertSee('Better.')
            ->assertSee('A single platform to browse available equipment, submit borrowing requests, track approvals, and manage return deadlines efficiently.')
            ->assertSee('Approved')->assertSee('Pending Request')->assertSee('Due Soon');

        // navigation: anchors point at sections that exist, plus working auth routes
        foreach (['features', 'how', 'highlights'] as $id) {
            $html->assertSee('href="#' . $id . '"', false)->assertSee('id="' . $id . '"', false);
        }
        $html->assertSee('href="' . route('login') . '"', false)->assertSee('href="' . route('register') . '"', false)
            ->assertSee('How It Works')->assertSee('Highlights')->assertSee('Log In')->assertSee('Create Account');

        // all six features, six steps, six highlights
        foreach (['Browse Available Equipment', 'Easy Borrowing Requests', 'Real-Time Request Tracking', 'Return Deadline Monitoring', 'Equipment Management', 'User-Friendly Dashboard',
                  'Create an Account', 'Submit Borrowing Request', 'Wait for Administrator Approval', 'Borrow Equipment', 'Return Equipment on Time',
                  'Organized Equipment Records', 'Faster Borrowing Process', 'Easy Request Monitoring', 'Better Equipment Management', 'Reduced Manual Paperwork', 'Efficient Return Monitoring'] as $text) {
            $html->assertSee($text);
        }

        // call to action + footer
        $html->assertSee('Ready to Borrow Smarter?')->assertSee('Get Started')
            ->assertSee('Experience a faster and more organized way to borrow and manage school equipment with EquipTrack.')
            ->assertSee('Quick Links')->assertSee('Contact Information')->assertSee('All Rights Reserved')
            ->assertSee('src="' . asset('js/landing.js') . '"', false);
    }

    public function test_landing_page_shows_real_numbers_and_the_dashboard_button_when_logged_in(): void
    {
        $this->seed();
        // demo data: 11 equipment types across 5 categories, 4 borrowers
        $this->get('/')->assertOk()->assertSee('data-count="5"', false)->assertSee('data-count="4"', false);

        $borrower = User::where('email', 'borrower@demo.test')->first();
        $this->actingAs($borrower)->get('/')->assertOk()->assertSee('Go to dashboard')->assertSee('href="' . route('dashboard') . '"', false);
    }

    public function test_admin_dashboard_shows_real_values_and_never_raw_blade_or_php(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@demo.test')->first();

        $page = $this->actingAs($admin)->get('/admin')->assertOk();

        // nothing uncompiled may reach the browser
        foreach (['@php', '@endphp', '{{ $', '{{ explode', '$greeting', '$tones', '$stats', '$insights', 'auth()->user()', '<?php'] as $raw) {
            $page->assertDontSee($raw, false);
        }

        // real values: greeting + first name, the four insight cards, charts and activity
        $page->assertSeeText('Demo')->assertSee('Equipment utilization')->assertSee('Due in next 3 days')
            ->assertSee('Requests this month')->assertSee('Low-stock items')
            ->assertSee('id="chartMonthly"', false)->assertSee('id="chartAvail"', false)->assertSee('id="chartStatus"', false)
            ->assertSee('Maria Santos')->assertSee('LCD Projector');   // seeded borrower + equipment names in the activity feed
        $this->assertMatchesRegularExpression('/Good (morning|afternoon|evening), Demo/', $page->getContent());
    }

    public function test_dashboard_greetings_use_the_configured_local_timezone(): void
    {
        $this->seed();
        \Carbon\Carbon::setTestNow(new \Carbon\Carbon('2026-11-13 13:30:00', 'UTC'));

        try {
            $admin = User::where('email', 'admin@demo.test')->first();
            $this->actingAs($admin)->get('/admin')->assertSeeText('Good evening, Demo');

            $borrower = User::where('email', 'borrower@demo.test')->first();
            $this->actingAs($borrower)->get('/borrower')->assertSeeText('Good evening,');
        } finally {
            \Carbon\Carbon::setTestNow();
        }
    }

    public function test_no_view_mixes_one_line_php_with_php_blocks(): void
    {
        // Blade turns everything between the FIRST "@php" and the first "@endphp" into one raw PHP block.
        // A one-line @php(...) before an @endphp therefore prints template code as text.
        $bad = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $file) {
            if (!str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $src = file_get_contents($file->getPathname());
            $last = strrpos($src, '@endphp');
            if ($last === false) {
                continue;
            }
            if (preg_match_all('/@php\s*\(/', $src, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as [$text, $offset]) {
                    if ($offset < $last) {
                        $bad[] = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $file->getPathname());
                        break;
                    }
                }
            }
        }
        $this->assertSame([], $bad, 'These views mix @php(...) with @php ... @endphp: ' . implode(', ', $bad));
    }

    public function test_buttons_and_equipment_images_use_the_fixed_styles(): void
    {
        $css = file_get_contents(public_path('css/app.css'));

        // icon + label buttons must never be squeezed into the 30px icon box again
        $this->assertStringNotContainsString(':has(> i:only-child)', $css);
        $this->assertStringContainsString('.btn-icon {', $css);
        $this->assertMatchesRegularExpression('/\.btn \{[^}]*display: inline-flex;[^}]*gap:/', $css);

        // equipment images are contained, never cropped
        $this->assertMatchesRegularExpression('/\.eq-card \.media img \{[^}]*object-fit: contain/', $css);
        $this->assertDoesNotMatchRegularExpression('/\.eq-card \.media img \{[^}]*object-fit: cover/', $css);
        $this->assertMatchesRegularExpression('/\.eq-hero img \{[^}]*object-fit: contain/', $css);

        // icon-only action buttons in the equipment table opted in; labelled buttons did not
        $this->seed();
        $admin = User::where('email', 'admin@demo.test')->first();
        $this->actingAs($admin)->get('/admin/equipment')->assertOk()->assertSee('btn btn-sm btn-icon', false);
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('btn btn-light-soft btn-sm', false)->assertDontSee('btn-light-soft btn-sm btn-icon', false);
    }
}
