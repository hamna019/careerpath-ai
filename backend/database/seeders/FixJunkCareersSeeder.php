<?php

namespace Database\Seeders;

use App\Models\CareerDetails;
use Illuminate\Database\Seeder;

class FixJunkCareersSeeder extends Seeder
{
    public function run(): void
    {
        $surgeon = CareerDetails::find(10);
        if ($surgeon) {
            $surgeon->update([
                'title' => 'Senior Surgeon',
                'match' => '88% Match',
                'demand' => 'Very High Demand',
                'description' => 'Perform complex surgical procedures to treat injuries, diseases, and deformities, leading operating room teams in high-stakes, life-saving care.',
                'role_overview' => 'Senior Surgeons diagnose conditions requiring operative treatment, plan surgical approaches, and lead operating room teams through complex procedures. The path requires medical school, a surgical residency of 5+ years, and often a fellowship in a subspecialty such as cardiac, orthopedic, or neurosurgery.',
                'skills' => [
                    ['name' => 'Surgical Technique', 'value' => 95],
                    ['name' => 'Anatomy & Physiology', 'value' => 90],
                    ['name' => 'Decision-Making Under Pressure', 'value' => 92],
                    ['name' => 'Team Leadership', 'value' => 80],
                    ['name' => 'Patient Care', 'value' => 85],
                ],
                'salary' => '$300,000 - $450,000',
                'growth' => 'High',
                'timeline' => '10 - 14 years',
                'openings' => '12,000+',
                'experience' => 'MD + surgical residency required',
                'flexibility' => 'On-site',
                'icon' => 'Stethoscope',
                'roadmap' => [
                    ['title' => 'Complete Pre-Med & MCAT Prep', 'desc' => 'Finish undergraduate pre-med coursework and score competitively on the MCAT.', 'duration' => '3-4 years', 'tags' => ['MCAT Prep', 'AAMC', 'Khan Academy']],
                    ['title' => 'Attend Medical School', 'desc' => 'Complete four years of medical school, excelling in surgery rotations to stand out for residency matching.', 'duration' => '4 years', 'tags' => ['USMLE Step 1', 'Surgical Clerkships', 'AAMC']],
                    ['title' => 'Complete a Surgical Residency', 'desc' => 'Undergo 5-7 years of intensive surgical residency training across general and specialized procedures.', 'duration' => '5-7 years', 'tags' => ['NRMP Match', 'ACGME', 'American College of Surgeons']],
                    ['title' => 'Pursue Fellowship & Board Certification', 'desc' => 'Specialize further through a fellowship and pass board certification exams in your surgical subspecialty.', 'duration' => '1-3 years', 'tags' => ['ABS Board Exam', 'Subspecialty Fellowship']],
                ],
                'courses' => [
                    ['title' => 'MCAT Complete Prep Course', 'platform' => 'Kaplan', 'rating' => '4.6', 'cost' => '$2,199'],
                    ['title' => 'USMLE Step 1 & 2 Prep', 'platform' => 'UWorld', 'rating' => '4.8', 'cost' => '$399/6mo'],
                    ['title' => 'Surgical Anatomy Fundamentals', 'platform' => 'Coursera (Michigan)', 'rating' => '4.8', 'cost' => '$49/mo'],
                    ['title' => 'Fundamentals of Laparoscopic Surgery', 'platform' => 'American College of Surgeons', 'rating' => '4.7', 'cost' => '$300 (exam)'],
                ],
            ]);
        }

        $civil = CareerDetails::find(26);
        if ($civil) {
            $civil->update([
                'title' => 'Civil Engineer',
                'match' => '80% Match',
                'demand' => 'High Demand',
                'description' => 'Design, build, and maintain the infrastructure that shapes modern life, from roads and bridges to water systems and buildings.',
                'role_overview' => 'Civil Engineers plan, design, and oversee construction of infrastructure projects such as roads, bridges, dams, and buildings. They balance structural safety, environmental impact, and budget constraints while coordinating with architects, contractors, and government agencies throughout a project\'s lifecycle.',
                'skills' => [
                    ['name' => 'Structural Analysis', 'value' => 88],
                    ['name' => 'AutoCAD/Civil 3D', 'value' => 82],
                    ['name' => 'Site Surveying', 'value' => 75],
                    ['name' => 'Project Management', 'value' => 78],
                    ['name' => 'Building Codes & Regulations', 'value' => 72],
                ],
                'salary' => '$70,000 - $115,000',
                'growth' => 'High',
                'timeline' => '4 - 5 years',
                'openings' => '24,000+',
                'experience' => 'Engineering degree required',
                'flexibility' => 'On-site',
                'icon' => 'Building2',
                'roadmap' => [
                    ['title' => 'Complete a Civil Engineering Degree', 'desc' => 'Study structural analysis, geotechnical engineering, and fluid mechanics in an accredited program.', 'duration' => '4 years', 'tags' => ['ABET-Accredited Program', 'MIT OpenCourseWare']],
                    ['title' => 'Master CAD & Design Software', 'desc' => 'Get proficient in AutoCAD Civil 3D and structural analysis tools used across the industry.', 'duration' => '2-3 months', 'tags' => ['AutoCAD Civil 3D', 'STAAD.Pro', 'Revit']],
                    ['title' => 'Gain Field & Site Experience', 'desc' => 'Work internships involving site surveying, construction inspection, or infrastructure design.', 'duration' => '6-12 months', 'tags' => ['Internships', 'Construction Firms', 'Government Agencies']],
                    ['title' => 'Pursue FE/PE Licensure', 'desc' => 'Pass the Fundamentals of Engineering exam and work toward Professional Engineer licensure.', 'duration' => '4+ years experience', 'tags' => ['NCEES FE Exam', 'PE License']],
                ],
                'courses' => [
                    ['title' => 'Civil Engineering Fundamentals', 'platform' => 'edX (MIT)', 'rating' => '4.6', 'cost' => 'Free / $99 verified'],
                    ['title' => 'AutoCAD Civil 3D Essential Training', 'platform' => 'LinkedIn Learning', 'rating' => '4.6', 'cost' => '$29.99/mo'],
                    ['title' => 'Structural Engineering Basics', 'platform' => 'Coursera (Michigan)', 'rating' => '4.6', 'cost' => '$49/mo'],
                    ['title' => 'FE Civil Exam Prep', 'platform' => 'School of PE', 'rating' => '4.6', 'cost' => '$799'],
                ],
            ]);
        }
    }
}
