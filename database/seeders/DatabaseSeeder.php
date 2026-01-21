<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\Invitation;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

    
        // Create Super Admin (no company_id)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@shorturl.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        // Create Companies
        $companies = [
            [
                'name' => 'TechCorp Solutions',
                'email' => 'info@techcorp.com',
            ],
            [
                'name' => 'Marketing Pro',
                'email' => 'contact@marketingpro.com',
            ],
            [
                'name' => 'E-Shop Online',
                'email' => 'support@eshop.com',
            ],
            [
                'name' => 'HealthPlus',
                'email' => 'hello@healthplus.com',
            ],
            [
                'name' => 'TravelHub',
                'email' => 'bookings@travelhub.com',
            ],
        ];

        $createdCompanies = [];

        foreach ($companies as $companyData) {
            $company = Company::create($companyData);
            $createdCompanies[] = $company;
        }

        // Store all users to be created
        $allUsersToCreate = [$superAdmin];
        
        // First, collect all user data
        foreach ($createdCompanies as $company) {
            // Company admin data
            $companyAdmin = [
                'name' => $this->getCompanyAdminName($company->name),
                'email' => $this->getCompanyAdminEmail($company->email),
                'password' => Hash::make('password'),
                'role' => 'admin',
                'company_id' => $company->id,
                'email_verified_at' => now(),
            ];
            
            $allUsersToCreate[] = $companyAdmin;

            // Member users data for each company (2-3 members)
            $memberCount = rand(2, 3);
            for ($i = 1; $i <= $memberCount; $i++) {
                $member = [
                    'name' => $this->getMemberName($i),
                    'email' => $this->getMemberEmail($company->email, $i),
                    'password' => Hash::make('password'),
                    'role' => 'member',
                    'company_id' => $company->id,
                    'email_verified_at' => now(),
                ];
                $allUsersToCreate[] = $member;
            }
        }

        // Now create all users in the database
        $allUsers = [];
        foreach ($allUsersToCreate as $userData) {
            if ($userData instanceof User) {
                // This is the super admin already created
                $allUsers[] = $userData;
            } else {
                // Create new user
                $user = User::create($userData);
                $allUsers[] = $user;
            }
        }

        // Now create short URLs for each company
        foreach ($createdCompanies as $company) {
            // Get users from this company
            $companyUsers = [];
            foreach ($allUsers as $user) {
                if ($user->company_id == $company->id) {
                    $companyUsers[] = $user;
                }
            }
            
            // Create short URLs for each company (5-12 URLs)
            $urlCount = rand(5, 12);
            for ($j = 0; $j < $urlCount; $j++) {
                // Make sure we have users for this company
                if (count($companyUsers) > 0) {
                    $randomUser = $companyUsers[array_rand($companyUsers)];
                    
                    ShortUrl::create([
                        'company_id' => $company->id,
                        'user_id' => $randomUser->id,
                        'short_code' => $this->generateShortCode(),
                        'long_url' => $this->getLongUrlForCompany($company->name, $j),
                        'hits' => rand(0, 1000),
                        'created_at' => now()->subDays(rand(0, 90))->subHours(rand(0, 23)),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Create some pending invitations for each company
            $invitationCount = rand(1, 3);
            for ($k = 0; $k < $invitationCount; $k++) {
                // Find a company admin to be the inviter
                $companyAdmin = null;
                foreach ($companyUsers as $user) {
                    if ($user->role == 'admin') {
                        $companyAdmin = $user;
                        break;
                    }
                }
                
                if ($companyAdmin) {
                    Invitation::create([
                        'company_id' => $company->id,
                        'invited_by' => $companyAdmin->id,
                        'email' => 'invited' . ($k + 1) . '@example.com',
                        'token' => Str::random(60),
                        'role' => rand(0, 1) ? 'admin' : 'member',
                        'status' => 'pending',
                        'expires_at' => now()->addDays(7),
                        'created_at' => now()->subDays(rand(0, 5)),
                    ]);
                }
            }
        }

        // Create some expired invitations
        foreach ($createdCompanies as $company) {
            if (rand(0, 1)) { // 50% chance to create expired invitation
                // Get users from this company
                $companyUsers = [];
                foreach ($allUsers as $user) {
                    if ($user->company_id == $company->id) {
                        $companyUsers[] = $user;
                    }
                }
                
                // Find a company admin
                $companyAdmin = null;
                foreach ($companyUsers as $user) {
                    if ($user->role == 'admin') {
                        $companyAdmin = $user;
                        break;
                    }
                }
                
                if ($companyAdmin) {
                    Invitation::create([
                        'company_id' => $company->id,
                        'invited_by' => $companyAdmin->id,
                        'email' => 'expired@example.com',
                        'token' => Str::random(60),
                        'role' => 'member',
                        'status' => 'expired',
                        'expires_at' => now()->subDays(10),
                        'created_at' => now()->subDays(15),
                    ]);
                }
            }
        }

        // Create some accepted invitations (already converted to users)
        foreach ($createdCompanies as $company) {
            if (rand(0, 1)) { // 50% chance
                // Get users from this company
                $companyUsers = [];
                foreach ($allUsers as $user) {
                    if ($user->company_id == $company->id) {
                        $companyUsers[] = $user;
                    }
                }
                
                // Find a company admin
                $companyAdmin = null;
                foreach ($companyUsers as $user) {
                    if ($user->role == 'admin') {
                        $companyAdmin = $user;
                        break;
                    }
                }
                
                if ($companyAdmin) {
                    // Find a member user to associate with accepted invitation
                    $memberUser = null;
                    foreach ($companyUsers as $user) {
                        if ($user->role == 'member') {
                            $memberUser = $user;
                            break;
                        }
                    }
                    
                    if ($memberUser) {
                        Invitation::create([
                            'company_id' => $company->id,
                            'invited_by' => $companyAdmin->id,
                            'email' => $memberUser->email,
                            'token' => Str::random(60),
                            'role' => 'member',
                            'status' => 'accepted',
                            'expires_at' => now()->addDays(7),
                            'created_at' => now()->subDays(20),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('========================================');
        $this->command->info('Super Admin Credentials:');
        $this->command->info('Email: admin@shorturl.com');
        $this->command->info('Password: password');
        $this->command->info('========================================');
        $this->command->info('Company Admin Credentials:');
        foreach ($createdCompanies as $company) {
            $companyAdmin = User::where('company_id', $company->id)
                ->where('role', 'admin')
                ->first();
            if ($companyAdmin) {
                $this->command->info("Company: {$company->name}");
                $this->command->info("Email: {$companyAdmin->email}");
                $this->command->info("Password: password");
                $this->command->info('---');
            }
        }
        $this->command->info('========================================');
    }

    /**
     * Generate a random short code
     */
    private function generateShortCode(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

    /**
     * Get company admin name based on company
     */
    private function getCompanyAdminName(string $companyName): string
    {
        $names = [
            'TechCorp Solutions' => 'John Smith',
            'Marketing Pro' => 'Sarah Johnson',
            'E-Shop Online' => 'Mike Wilson',
            'HealthPlus' => 'Dr. Emily Chen',
            'TravelHub' => 'Robert Taylor',
        ];
        
        return $names[$companyName] ?? 'Admin User';
    }

    /**
     * Get company admin email
     */
    private function getCompanyAdminEmail(string $companyEmail): string
    {
        $domain = explode('@', $companyEmail)[1];
        $companyName = strtolower(explode('@', $companyEmail)[0]);
        
        $admins = [
            'techcorp' => 'john',
            'marketingpro' => 'sarah',
            'eshop' => 'mike',
            'healthplus' => 'emily',
            'travelhub' => 'robert',
        ];
        
        $username = $admins[$companyName] ?? 'admin';
        return $username . '@' . $domain;
    }

    /**
     * Get member name
     */
    private function getMemberName(int $index): string
    {
        $firstNames = ['Alex', 'Jessica', 'David', 'Lisa', 'Michael', 'Emma', 'Chris', 'Olivia', 'Daniel', 'Sophia'];
        $lastNames = ['Morgan', 'Lee', 'Brown', 'Wong', 'Davis', 'Taylor', 'Miller', 'Anderson', 'Thomas', 'White'];
        
        return $firstNames[$index % count($firstNames)] . ' ' . $lastNames[($index + 1) % count($lastNames)];
    }

    /**
     * Get member email
     */
    private function getMemberEmail(string $companyEmail, int $index): string
    {
        $domain = explode('@', $companyEmail)[1];
        $usernames = ['alex', 'jessica', 'david', 'lisa', 'michael', 'emma'];
        
        $username = $usernames[$index % count($usernames)];
        return $username . '@' . $domain;
    }

    /**
     * Get long URL based on company type
     */
    private function getLongUrlForCompany(string $companyName, int $index): string
    {
        $baseUrls = [
            'TechCorp Solutions' => [
                'https://techcorp.com/product/software-' . rand(100, 999),
                'https://docs.techcorp.com/api/v' . rand(1, 3),
                'https://status.techcorp.com',
                'https://blog.techcorp.com/post-' . rand(100, 999),
                'https://support.techcorp.com/ticket/' . rand(10000, 99999),
                'https://techcorp.com/careers',
                'https://techcorp.com/contact',
            ],
            'Marketing Pro' => [
                'https://marketingpro.com/campaign-' . rand(1, 50),
                'https://webinar.marketingpro.com/register',
                'https://ebook.marketingpro.com/download',
                'https://survey.marketingpro.com',
                'https://marketingpro.com/promo/summer-sale',
                'https://newsletter.marketingpro.com',
                'https://marketingpro.com/case-studies',
            ],
            'E-Shop Online' => [
                'https://eshop.com/product/' . rand(1000, 9999),
                'https://eshop.com/category/electronics',
                'https://eshop.com/deal/flash-sale',
                'https://eshop.com/cart',
                'https://eshop.com/checkout',
                'https://eshop.com/track-order/' . rand(100000, 999999),
                'https://eshop.com/reviews',
            ],
            'HealthPlus' => [
                'https://healthplus.com/appointment',
                'https://healthplus.com/article/health-tips',
                'https://healthplus.com/lab/results',
                'https://healthplus.com/telemedicine',
                'https://healthplus.com/vaccine',
                'https://healthplus.com/doctors',
                'https://healthplus.com/emergency',
            ],
            'TravelHub' => [
                'https://travelhub.com/flight/booking/' . rand(100000, 999999),
                'https://travelhub.com/hotel/details/' . rand(1000, 9999),
                'https://travelhub.com/package/tour-' . rand(1, 50),
                'https://travelhub.com/car-rental',
                'https://travelhub.com/checkin',
                'https://travelhub.com/reviews',
                'https://travelhub.com/destinations',
            ],
        ];

        $urls = $baseUrls[$companyName] ?? [
            'https://example.com/about',
            'https://example.com/services',
            'https://example.com/contact',
            'https://example.com/blog',
            'https://example.com/careers',
            'https://example.com/faq',
        ];

        return $urls[$index % count($urls)] . (rand(0, 1) ? '?ref=' . $this->generateShortCode() : '');
    }
}