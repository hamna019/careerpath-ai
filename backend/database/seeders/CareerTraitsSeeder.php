<?php

namespace Database\Seeders;

use App\Models\CareerDetails;
use Illuminate\Database\Seeder;

class CareerTraitsSeeder extends Seeder
{
    public function run(): void
    {
        $map = collect(self::profiles())->mapWithKeys(fn ($v, $k) => [mb_strtolower($k) => $v]);
        $updated = 0;

        foreach (CareerDetails::all() as $career) {
            $profile = $map->get(mb_strtolower(trim($career->title)));
            if (!$profile) {
                continue;
            }

            [$riasec, $domains, $edu] = explode('|', $profile);
            $career->update(['traits' => [
                'riasec'  => str_split($riasec),
                'domains' => explode(',', $domains),
                'edu'     => $edu,
            ]]);
            $updated++;
        }

        $this->command?->info("Profiled {$updated} careers.");
    }

    public static function profiles(): array
    {
        return [
            'Software Developer'                 => 'IRC|tech|bachelor',
            'Full Stack Developer'               => 'IRC|tech|self',
            'Frontend Developer'                 => 'IAR|tech,design|self',
            'Backend Developer'                  => 'IRC|tech|self',
            'Mobile App Developer'               => 'IRA|tech|self',
            'Game Developer'                     => 'IRA|tech,arts|bachelor',
            'Game Designer'                      => 'AIE|arts,tech,design|bachelor',
            'AI Engineer'                        => 'IRC|tech,data|bachelor',
            'Machine Learning Engineer'          => 'IRC|tech,data|master',
            'Data Scientist'                     => 'IRC|data,tech,science|master',
            'Data Analyst'                       => 'ICE|data,business|bachelor',
            'Data Engineer'                      => 'ICR|data,tech|bachelor',
            'Database Administrator'             => 'CIR|tech,data|bachelor',
            'Cybersecurity Analyst'              => 'ICR|cyber,tech|bachelor',
            'Ethical Hacker / Penetration Tester' => 'IRC|cyber,tech|self',
            'Cloud Solutions Architect'          => 'ICE|tech|bachelor',
            'Cloud Engineer'                     => 'RIC|tech|self',
            'DevOps Engineer'                    => 'RIC|tech|bachelor',
            'Site Reliability Engineer (SRE)'    => 'IRC|tech|bachelor',
            'Network Engineer'                   => 'RIC|tech,cyber|bachelor',
            'IT Support Specialist'              => 'RCS|tech|short',
            'Data Center Technician'             => 'RCI|tech,trades|short',
            'QA Automation Engineer'             => 'CIR|tech|self',
            'Blockchain Developer'               => 'ICR|tech,finance|self',
            'Embedded Systems & IoT Engineer'    => 'IRC|tech,eng|bachelor',
            'Semiconductor Chip Design Engineer' => 'IRC|eng,tech|master',
            'AR/VR Developer'                    => 'IAR|tech,design|bachelor',
            'Quantum Computing Engineer'         => 'IRC|science,tech|long',
            'AI Ethics & Governance Specialist'  => 'ISE|law,tech,gov|master',
            'Drone / UAV Pilot'                  => 'RIC|transport,tech,media|short',
            'IT Project Manager'                 => 'ECI|tech,business|bachelor',
            'Product Manager'                    => 'EIA|tech,business|bachelor',
            'SAP / ERP Consultant'               => 'CEI|tech,business|bachelor',
            'Technical Writer'                   => 'AIC|tech,media|bachelor',
            'UX Designer'                        => 'AIS|design,tech|self',

            'Business Analyst'                   => 'ICE|business,data|bachelor',
            'Business Development Manager'       => 'ESC|business|bachelor',
            'Entrepreneur / Startup Founder'     => 'EAI|business|self',
            'Management Consultant'              => 'EIC|business|master',
            'Operations Manager'                 => 'ECR|business|bachelor',
            'Project Manager (PMP)'              => 'ECS|business|bachelor',
            'Supply Chain Manager'               => 'ECI|business,transport|bachelor',
            'Customer Success Manager'           => 'SEC|business,tech|bachelor',
            'Human Resources Specialist'         => 'SEC|business,social|bachelor',
            'Marketing Manager'                  => 'EAC|business,media|bachelor',
            'Digital Marketing Specialist'       => 'EAC|media,business|self',
            'Social Media Manager'               => 'EAS|media,business|self',
            'SEO Specialist'                     => 'EIC|media,tech,business|self',
            'E-commerce Manager'                 => 'ECA|business,tech|self',
            'Public Relations Specialist'        => 'EAS|media,business|bachelor',
            'Real Estate Agent'                  => 'ECS|business|short',
            'Virtual Assistant'                  => 'CSE|business|short',
            'Sustainability / ESG Consultant'    => 'EIS|env,business|bachelor',
            'Financial Analyst'                  => 'CIE|finance,data|bachelor',
            'Financial Advisor / Wealth Manager' => 'ECS|finance|bachelor',
            'Investment Banker'                  => 'EIC|finance,business|master',
            'Quantitative Analyst (Quant)'       => 'ICE|finance,data|master',
            'Certified Public Accountant'        => 'CEI|finance,business|bachelor',
            'Chartered Accountant (CA / ACCA)'   => 'CEI|finance,business|master',
            'Banking Officer / Relationship Manager' => 'ECS|finance,business|bachelor',
            'Insurance Underwriter'              => 'CEI|finance|bachelor',
            'Actuary'                            => 'ICE|finance,data|bachelor',
            'Statistician'                       => 'ICE|data,science|master',
            'Economist'                          => 'ICE|finance,gov,data|master',

            'General Physician'                  => 'ISR|health|long',
            'Physician (General Practitioner)'   => 'ISR|health|long',
            'Senior Surgeon'                     => 'IRS|health|long',
            'Cardiologist'                       => 'ISR|health|long',
            'Dermatologist'                      => 'ISR|health|long',
            'Pediatrician'                       => 'SIR|health|long',
            'Gynecologist / Obstetrician'        => 'ISR|health|long',
            'Anesthesiologist'                   => 'IRC|health|long',
            'Radiologist'                        => 'ICR|health|long',
            'Orthopedic Surgeon'                 => 'RIS|health,sports|long',
            'Psychiatrist'                       => 'ISA|health,social|long',
            'Dentist'                            => 'ISR|health|long',
            'Optometrist'                        => 'ISR|health|long',
            'Pharmacist'                         => 'ICS|health,science|long',
            'Veterinarian'                       => 'IRS|health,agri,science|long',
            'Nurse Practitioner'                 => 'SIR|health|master',
            'Physician Assistant'                => 'SIR|health|master',
            'Registered Nurse'                   => 'SIR|health|bachelor',
            'Physical Therapist'                 => 'SIR|health,sports|master',
            'Occupational Therapist'             => 'SIR|health,social|master',
            'Speech-Language Pathologist'        => 'SIA|health,edu|master',
            'Clinical Psychologist'              => 'SIA|health,social|long',
            'Mental Health Counselor'            => 'SIA|health,social|master',
            'Dietitian / Nutritionist'           => 'SIC|health,sports|bachelor',
            'Medical Laboratory Scientist'       => 'IRC|health,science|bachelor',
            'Radiologic Technologist'            => 'RIS|health|diploma',
            'Paramedic / EMT'                    => 'SRI|health,defense|diploma',
            'Health Informatics Specialist'      => 'CIS|health,data,tech|bachelor',
            'Public Health Specialist / Epidemiologist' => 'ISC|health,science,gov|master',
            'Clinical Research Associate'        => 'CIS|health,science|bachelor',
            'Biotechnologist'                    => 'IRC|science,health,agri|master',

            'Civil Engineer'                     => 'RIC|eng|bachelor',
            'Mechanical Engineer'                => 'RIC|eng|bachelor',
            'Electrical Engineer'                => 'IRC|eng|bachelor',
            'Aerospace Engineer'                 => 'IRC|eng,transport,science|bachelor',
            'Chemical Engineer'                  => 'IRC|eng,science|bachelor',
            'Environmental Engineer'             => 'IRS|env,eng|bachelor',
            'Petroleum Engineer'                 => 'IRE|eng,env|bachelor',
            'Telecommunications Engineer'        => 'IRC|eng,tech|bachelor',
            'Robotics Engineer'                  => 'RIC|eng,tech|bachelor',
            'Biomedical Engineer'                => 'IRS|eng,health,science|bachelor',
            'Electric Vehicle (EV) Engineer'     => 'IRC|eng,env|bachelor',
            'Renewable Energy Engineer'          => 'IRE|env,eng|bachelor',
            'Industrial Engineer'                => 'IEC|eng,business|bachelor',
            'Construction Manager'               => 'ERC|eng,trades|bachelor',
            'Aircraft Maintenance Engineer'      => 'RIC|transport,trades,eng|diploma',

            'Research Scientist'                 => 'IRA|science|long',
            'Astrophysicist'                     => 'IRA|science|long',
            'Astronaut'                          => 'IRE|science,eng,transport,defense|long',
            'Geologist'                          => 'IRC|science,env|bachelor',
            'Environmental Scientist'            => 'IRS|env,science|bachelor',
            'Marine Biologist'                   => 'IRS|science,env|master',
            'Forensic Scientist'                 => 'IRC|science,law|bachelor',
            'Agricultural Scientist'             => 'IRS|agri,science,env|master',
            'Farm Manager / Agronomist'          => 'REI|agri,env|bachelor',
            'Food Scientist / Food Technologist' => 'IRC|science,agri,health|bachelor',

            'Corporate Lawyer'                   => 'EIC|law,business|long',
            'Criminal Lawyer'                    => 'ESI|law|long',
            'Judge'                              => 'EIS|law,gov|long',
            'Compliance Officer'                 => 'CEI|law,finance|bachelor',
            'Civil Services Officer (CSS / PCS)' => 'ESC|gov,law|bachelor',
            'Diplomat / Foreign Service Officer' => 'ESA|gov,law|master',
            'Intelligence Analyst'               => 'ICE|defense,gov,data|bachelor',
            'Urban and Regional Planner'         => 'IEA|gov,design,env|master',
            'Military Officer'                   => 'ERS|defense,gov|bachelor',
            'Police Officer / Detective'         => 'RES|defense,law|short',
            'Firefighter'                        => 'RSE|defense,health|short',
            'Social Worker'                      => 'SEC|social,gov|bachelor',

            'High School Teacher'                => 'SAI|edu|bachelor',
            'Special Education Teacher'          => 'SAI|edu,social|bachelor',
            'University Professor'               => 'ISA|edu,science|long',
            'Instructional Designer / E-learning Developer' => 'ASI|edu,design,tech|bachelor',

            'Graphic Designer'                   => 'AER|design,media|diploma',
            'Industrial Designer'                => 'ARI|design,eng|bachelor',
            'Interior Designer'                  => 'AER|design|diploma',
            'Fashion Designer'                   => 'AER|design,arts|diploma',
            'Architect'                          => 'AIR|design,eng|master',
            'Animator / VFX Artist'              => 'ARI|design,arts,media|diploma',
            'Makeup Artist'                      => 'ARS|design,arts,hospitality|short',
            'Photographer'                       => 'ARE|arts,media|short',
            'Video Editor'                       => 'ARC|media,arts|self',
            'Film Director'                      => 'AES|arts,media|bachelor',
            'Actor'                              => 'AES|arts,media|diploma',
            'Voice Actor'                        => 'AES|arts,media|short',
            'Music Producer'                     => 'AER|arts,media|self',
            'Sound Engineer'                     => 'RAI|media,arts,tech|diploma',
            'Author / Writer'                    => 'AIS|arts,media|self',
            'Copywriter / Content Writer'        => 'AEI|media,arts|self',
            'Content Creator'                    => 'AES|media,arts|self',
            'Journalist'                         => 'AES|media,gov|bachelor',
            'News Anchor / TV Presenter'         => 'AES|media|bachelor',
            'Translator / Interpreter'           => 'ASC|gov,hospitality,media|bachelor',

            'Executive Chef'                     => 'ARE|hospitality|diploma',
            'Sommelier'                          => 'EAS|hospitality|short',
            'Hotel Manager'                      => 'ESC|hospitality,business|bachelor',
            'Event Planner'                      => 'ECS|hospitality,business|diploma',
            'Tourism & Travel Manager'           => 'ESC|hospitality,transport|bachelor',
            'Flight Attendant / Cabin Crew'      => 'SER|hospitality,transport|short',
            'Commercial Airline Pilot'           => 'RIE|transport|diploma',
            'Air Traffic Controller'             => 'CRI|transport,defense|diploma',
            'Merchant Marine Officer'            => 'RCE|transport,defense|diploma',
            'Commercial Truck Driver'            => 'RC|transport|short',

            'Professional Athlete'               => 'RES|sports|self',
            'Sports Coach'                       => 'SER|sports,edu|diploma',
            'Personal Trainer'                   => 'SRE|sports,health|short',
            'Esports Professional / Pro Gamer'   => 'REA|sports,media,tech|self',

            'Electrician'                        => 'RCI|trades|diploma',
            'Plumber'                            => 'RC|trades|diploma',
            'HVAC Technician'                    => 'RCI|trades|diploma',
            'Welder'                             => 'RC|trades|diploma',
            'Automotive Technician'              => 'RIC|trades,transport|diploma',
            'Renewable Energy Technician'        => 'RIC|env,trades,eng|diploma',
        ];
    }
}
