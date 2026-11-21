<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CareerDetails;

class CareerDetailsSeeder extends Seeder
{
    public function run()
    {
        $careers = [
            [
                'title' => 'Software Developer',
                'match' => '92% Match',
                'demand' => 'Very High Demand',
                'description' => 'Design, build, and deploy production-grade applications. Break down complex business logic into scalable code, handle databases, and optimize software systems.',
                'role_overview' => 'Design, build, and deploy production-grade software applications. This role requires breaking complex business problems down into scalable, maintainable logic structures, writing testable code, optimizing application delivery pipelines, and managing relational data schemas.',
                'skills' => [
                    ['name' => 'JavaScript/Python', 'value' => 90],
                    ['name' => 'HTML/CSS', 'value' => 85],
                    ['name' => 'Git & GitHub', 'value' => 75],
                    ['name' => 'React/Vue', 'value' => 70],
                    ['name' => 'Problem Solving', 'value' => 95],
                    ['name' => 'Databases', 'value' => 65]
                ],
                'salary' => '$85,000 - $150,000',
                'growth' => 'High',
                'timeline' => '12 - 18 months',
                'openings' => '45,000+',
                'experience' => '3-5 years',
                'flexibility' => 'Remote OK',
                'icon' => 'Code2',
                'roadmap' => [
                    ['title' => 'Learn Programming Fundamentals', 'duration' => '2-3 months', 'desc' => 'Master the absolute basics of core programming syntax, control structures, loops, and functions using Python or JavaScript.', 'tags' => ['Codecademy', 'freeCodeCamp', 'Python.org']],
                    ['title' => 'Build Your First Projects', 'duration' => '2 months', 'desc' => 'Create 3-5 structural full-stack projects using semantic markup and component layouts to showcase responsive logic.', 'tags' => ['GitHub', 'Personal Portfolio', 'Open Source']],
                    ['title' => 'Learn Data Structures & Algorithms', 'duration' => '3-4 months', 'desc' => 'Understand runtime complexities (O(n) metrics), sorting algorithms, memory profiles, and core trees for elite engineering steps.', 'tags' => ['LeetCode', 'HackerRank', 'CS50']],
                    ['title' => 'Master a Framework & DevOps Tools', 'duration' => '3-4 months', 'desc' => 'Deep dive into modern reactive state models like React, deployment pipelines, container engines, and continuous integration flows.', 'tags' => ['Official Docs', 'YouTube', 'Udemy']]
                ],
                'courses' => [
                    ['title' => 'Complete Web Development Bootcamp', 'platform' => 'Udemy', 'rating' => '4.7', 'cost' => 'Free'],
                    ['title' => 'CS50: Introduction to Computer Science', 'platform' => 'Harvard/edX', 'rating' => '4.9', 'cost' => 'Free'],
                    ['title' => 'The Odin Project Full-Stack Path', 'platform' => 'Free Online', 'rating' => '4.8', 'cost' => 'Free'],
                    ['title' => 'Full Stack Open Ecosystem Certification', 'platform' => 'University of Helsinki', 'rating' => '4.9', 'cost' => 'Free']
                ]
            ],
            [
                'title' => 'Data Analyst',
                'match' => '87% Match',
                'demand' => 'High Demand',
                'description' => 'Transform raw organizational database payloads into actionable corporate intelligence. Audit pipelines and engineer premium executive dashboards.',
                'role_overview' => 'Transform raw corporate database payloads into actionable business intelligence strategies. Responsibilities include auditing backend pipelines, engineering mathematical metrics, processing statistical matrices, and styling clear data visualization dashboards.',
                'skills' => [
                    ['name' => 'SQL Data Queries', 'value' => 88],
                    ['name' => 'Python/R', 'value' => 75],
                    ['name' => 'Excel Advanced', 'value' => 90],
                    ['name' => 'Tableau/PowerBI', 'value' => 85],
                    ['name' => 'Statistics', 'value' => 80],
                    ['name' => 'Data Warehousing', 'value' => 60]
                ],
                'salary' => '$65,000 - $110,000',
                'growth' => 'Very High',
                'timeline' => '6 - 12 months',
                'openings' => '32,000+',
                'experience' => '1-3 years',
                'flexibility' => 'Hybrid',
                'icon' => 'BarChart3',
                'roadmap' => [
                    ['title' => 'Master Spreadsheet Software & Statistical Modeling', 'duration' => '1-2 months', 'desc' => 'Learn conditional logic lookups, array metrics formulas, dynamic formatting, and multi-variable analytics models in Excel.', 'tags' => ['Chandoo', 'Coursera']],
                    ['title' => 'Learn SQL Database Querying & Architecture', 'duration' => '2 months', 'desc' => 'Write production relational schemas, multi-table joins, subqueries, and advanced window partition functions flawlessly.', 'tags' => ['SQLZoo', 'Mode Analytics']],
                    ['title' => 'Data Visualization & Dashboard Tooling', 'duration' => '2 months', 'desc' => 'Build professional interactive executive monitoring experiences using structural configurations inside Tableau or PowerBI.', 'tags' => ['Tableau Public', 'Udemy']],
                    ['title' => 'Python Data Science Core Libraries', 'duration' => '3 months', 'desc' => 'Ingest and process complex data arrays with mathematical Pandas, numerical NumPy arrays, and customized plots.', 'tags' => ['Kaggle', 'DataCamp']]
                ],
                'courses' => [
                    ['title' => 'Google Data Analytics Professional Certificate', 'platform' => 'Coursera', 'rating' => '4.8', 'cost' => 'Free'],
                    ['title' => 'Data Analysis with Python Applied Specialization', 'platform' => 'freeCodeCamp', 'rating' => '4.9', 'cost' => 'Free'],
                    ['title' => 'SQL for Data Science Complex Queries Course', 'platform' => 'UC Davis/Coursera', 'rating' => '4.6', 'cost' => 'Free'],
                    ['title' => 'Advanced Tableau Dashboard Design Architecture', 'platform' => 'Udemy Academic', 'rating' => '4.7', 'cost' => 'Free']
                ]
            ],
            [
                'title' => 'UX Designer',
                'match' => '82% Match',
                'demand' => 'High Demand',
                'description' => 'Architect flawless user interactions and interface behaviors for cross-platform products using cognitive psychology maps and prototype flows.',
                'role_overview' => 'Architect clean, friction-free user interaction journeys and product designs across cross-platform environments. This requires behavioral psychology alignment, structural wireframing, component tokens, and iterative user testing.',
                'skills' => [
                    ['name' => 'Figma/Adobe XD', 'value' => 95],
                    ['name' => 'User Research', 'value' => 80],
                    ['name' => 'Wireframing', 'value' => 90],
                    ['name' => 'Prototyping', 'value' => 85],
                    ['name' => 'UI Design', 'value' => 88],
                    ['name' => 'Interaction Design', 'value' => 70]
                ],
                'salary' => '$70,000 - $120,000',
                'growth' => 'High',
                'timeline' => '8 - 14 months',
                'openings' => '20,000+',
                'experience' => '2-4 years',
                'flexibility' => 'Remote OK',
                'icon' => 'PenTool',
                'roadmap' => [
                    ['title' => 'Understand UX Design Principles & Psychology', 'duration' => '1-2 months', 'desc' => 'Study core interaction structures, behavioral layout laws (Jakob\'s Law), cognitive patterns, and usability matrices.', 'tags' => ['Interaction Design Org', 'Medium']],
                    ['title' => 'Master Figma UI Design & Systems Technology', 'duration' => '2 months', 'desc' => 'Learn responsive design layout constraints, variant component sets, unified color tokens, and structural prototyping states.', 'tags' => ['Figma YouTube', 'Udemy']],
                    ['title' => 'User Research Execution & Telemetry Analysis', 'duration' => '2 months', 'desc' => 'Perform qualitative feedback validation, build complex user personas tracking matrices, and configure interactive journey charts.', 'tags' => ['Coursera', 'UX Collective']],
                    ['title' => 'Wireframing, Hi-Fi Layouts & Production Case Studies', 'duration' => '2 months', 'desc' => 'Develop detailed interaction blueprints and document 3 comprehensive production-grade case study profiles for review panels.', 'tags' => ['Figma', 'Behance']]
                ],
                'courses' => [
                    ['title' => 'Google UX Design Professional Certificate', 'platform' => 'Coursera', 'rating' => '4.8', 'cost' => 'Free'],
                    ['title' => 'Product Design Course Frameworks Specialization', 'platform' => 'Udacity', 'rating' => '4.5', 'cost' => 'Free'],
                    ['title' => 'Figma UI/UX Essentials Masterclass Track', 'platform' => 'Skillshare', 'rating' => '4.7', 'cost' => 'Free'],
                    ['title' => 'UI Architecture & Interface Token Systems', 'platform' => 'Interaction Design Org', 'rating' => '4.8', 'cost' => 'Free']
                ]
            ],
            [
                'title' => 'Business Analyst',
                'match' => '78% Match',
                'demand' => 'Medium Demand',
                'description' => 'Translate company needs into explicit engineering user stories, business charts, systemic flowcharts, and functional specifications.',
                'role_overview' => 'Bridge the communication gap between business managers and product software engineering execution. Responsible for extracting company needs and translating them into technical specifications, flow charts, and functional requirements.',
                'skills' => [
                    ['name' => 'Requirements Gathering', 'value' => 92],
                    ['name' => 'Process Modeling', 'value' => 85],
                    ['name' => 'Agile/Scrum', 'value' => 80],
                    ['name' => 'Communication', 'value' => 95],
                    ['name' => 'Jira/Confluence', 'value' => 75]
                ],
                'salary' => '$68,000 - $105,000',
                'growth' => 'Medium',
                'timeline' => '6 - 12 months',
                'openings' => '18,000+',
                'experience' => '2-5 years',
                'flexibility' => 'On-site',
                'icon' => 'Briefcase',
                'roadmap' => [
                    ['title' => 'Business Analysis Fundamentals', 'duration' => '1-2 months', 'desc' => 'Learn BA lifecycle core values, stakeholder matrix layouts, and requirements elicitation methodologies.', 'tags' => ['IIBA Guide', 'Udemy']],
                    ['title' => 'Process Mapping & Documentation Standards', 'duration' => '2 months', 'desc' => 'Master professional functional flowcharting protocols, process flow diagrams, and advanced BPMN notations.', 'tags' => ['Lucidchart', 'Visio']],
                    ['title' => 'Agile & Scrum Delivery Frameworks', 'duration' => '2 months', 'desc' => 'Understand granular user stories formulation rules, product backlog grooming sessions, and sprint release rhythms.', 'tags' => ['Scrum Alliance', 'Atlassian']],
                    ['title' => 'Enterprise Data Analytics Fundamentals', 'duration' => '2-3 months', 'desc' => 'Study structural relational data extractions, baseline data modeling parameters, and key metric indicator computations.', 'tags' => ['SQL Queries', 'Tableau Reporting']]
                ],
                'courses' => [
                    ['title' => 'Business Analysis Foundations', 'platform' => 'LinkedIn Learning', 'rating' => '4.6', 'cost' => 'Free'],
                    ['title' => 'Agile Planning with Jira Systems', 'platform' => 'Coursera', 'rating' => '4.7', 'cost' => 'Free'],
                    ['title' => 'IIBA Certified Business Analysis Practitioner Prep', 'platform' => 'Udemy Academic', 'rating' => '4.8', 'cost' => 'Free'],
                    ['title' => 'Corporate Business Intelligence & Data Mapping', 'platform' => 'Microsoft Learn', 'rating' => '4.5', 'cost' => 'Free']
                ]
            ],
            [
                'title' => 'Digital Marketing Specialist',
                'match' => '75% Match',
                'demand' => 'High Demand',
                'description' => 'Configure programmatic tracking networks, search engine index parameters, digital ads funnels, and performance conversion pixels.',
                'role_overview' => 'Maximize customer growth conversion metrics across omni-channel acquisition funnels. Responsibilities include indexing crawler logic, programmatic advertising systems management, analytical pixel tracking, and targeted campaign allocation.',
                'skills' => [
                    ['name' => 'SEO Optimization', 'value' => 88],
                    ['name' => 'Google Analytics', 'value' => 85],
                    ['name' => 'Paid Ads Management', 'value' => 90],
                    ['name' => 'Content Creation', 'value' => 80],
                    ['name' => 'Email Marketing', 'value' => 75]
                ],
                'salary' => '$55,000 - $95,000',
                'growth' => 'High',
                'timeline' => '6 - 10 months',
                'openings' => '28,000+',
                'experience' => '1-3 years',
                'flexibility' => 'Remote OK',
                'icon' => 'TrendingUp',
                'roadmap' => [
                    ['title' => 'SEO Fundamentals & Index Structuring', 'duration' => '2 months', 'desc' => 'Learn semantic structural schema alterations, user intent index parameters, backend link profiling, and site performance tuning.', 'tags' => ['HubSpot', 'Moz']],
                    ['title' => 'PPC & Paid Social Programmatic Ads Engines', 'duration' => '2 months', 'desc' => 'Master target distribution matrices, audience tracking parameters, pixel deployment layers, and automated bid logic dashboards.', 'tags' => ['Google Skillshop']],
                    ['title' => 'Content Copywriting Funnels & Automated Pipelines', 'duration' => '2 months', 'desc' => 'Formulate strategic multi-step retention triggers, drip automation matrices, and structural user landing pages design setups.', 'tags' => ['Mailchimp Tech', 'Copywriting Basics']],
                    ['title' => 'Marketing Analytics & Multi-Touch Attribution', 'duration' => '2 months', 'desc' => 'Analyze customer acquisition streams, cohort lifecycle properties, tracking pixels data payload, and performance report layouts.', 'tags' => ['Google Analytics 4', 'Mixpanel Engine']]
                ],
                'courses' => [
                    ['title' => 'Google Digital Marketing Certificate', 'platform' => 'Coursera', 'rating' => '4.8', 'cost' => 'Free'],
                    ['title' => 'Inbound Marketing Certification', 'platform' => 'HubSpot Academy', 'rating' => '4.7', 'cost' => 'Free'],
                    ['title' => 'Advanced Google Ads Paid Search Masterclass', 'platform' => 'Udemy Professional', 'rating' => '4.6', 'cost' => 'Free'],
                    ['title' => 'Web Analytics Data Tracking and Audience Cohorts', 'platform' => 'SimpliLearn Portal', 'rating' => '4.5', 'cost' => 'Free']
                ]
            ],
            [
                'title' => 'Product Manager',
                'match' => '85% Match',
                'demand' => 'High Demand',
                'description' => 'Own product lifecycle frameworks. Govern development constraints across design, engineering, and sales squads to launch viable software models.',
                'role_overview' => 'Own the absolute lifecycle and resource priority boundaries of software products. This demands evaluating development constraints across engineering, UX design, and market operations teams to ship minimal viable models.',
                'skills' => [
                    ['name' => 'Product Strategy', 'value' => 92],
                    ['name' => 'Roadmapping', 'value' => 90],
                    ['name' => 'Market Research', 'value' => 85],
                    ['name' => 'A/B Metrics Testing', 'value' => 78],
                    ['name' => 'Leadership', 'value' => 95]
                ],
                'salary' => '$95,000 - $160,000',
                'growth' => 'High',
                'timeline' => '12 - 24 months',
                'openings' => '15,000+',
                'experience' => '4-7 years',
                'flexibility' => 'Hybrid',
                'icon' => 'Target',
                'roadmap' => [
                    ['title' => 'Product Discovery & Market Fit Mechanics', 'duration' => '3 months', 'desc' => 'Evaluate target user retention cohorts, manage continuous discovery loops, model minimum viable concepts, and chart market analysis grids.', 'tags' => ['Product School', 'Medium']],
                    ['title' => 'Product Roadmapping & Prioritization Matrix Engineering', 'duration' => '2 months', 'desc' => 'Master RICE parameter configurations, coordinate dynamic value matrices, and configure multi-stage milestone tracking views.', 'tags' => ['Jira Software', 'Productboard Tool']],
                    ['title' => 'UX Layout Blueprinting & Product Spec Writing', 'duration' => '2-3 months', 'desc' => 'Draft high-level functional product requirement document catalogs and translate user requirements patterns into technical features logs.', 'tags' => ['Figma Wireframes', 'Confluence Docs']],
                    ['title' => 'Product Telemetry & Post-Launch Growth Performance', 'duration' => '3 months', 'desc' => 'Track funnel telemetry properties, administer A/B programmatic split matrix tests, and analyze active acquisition churn loops.', 'tags' => ['Mixpanel Data', 'Amplitude Systems']]
                ],
                'courses' => [
                    ['title' => 'Brand Management & Product Delivery', 'platform' => 'Coursera', 'rating' => '4.6', 'cost' => 'Free'],
                    ['title' => 'Product Management 101', 'platform' => 'Udemy', 'rating' => '4.5', 'cost' => 'Free'],
                    ['title' => 'Product Analytics Core Engineering Frameworks', 'platform' => 'Amplitude Academy', 'rating' => '4.8', 'cost' => 'Free'],
                    ['title' => 'Enterprise Product Manager Master Certification', 'platform' => 'Product School', 'rating' => '4.7', 'cost' => 'Free']
                ]
            ],
            [
                'title' => 'Human Resources Specialist',
                'match' => '70% Match',
                'demand' => 'Medium Demand',
                'description' => 'Govern company employment loops, structural candidate screening protocols, workforce metrics, and labor legal compliance catalogs.',
                'role_overview' => 'Architect corporate employee talent optimization frameworks. This includes coordinating algorithmic candidate tracking files, refining company communication, and managing corporate legal compliance portfolios.',
                'skills' => [
                    ['name' => 'Talent Acquisition', 'value' => 90],
                    ['name' => 'Employee Relations', 'value' => 88],
                    ['name' => 'Conflict Resolution', 'value' => 85],
                    ['name' => 'HRIS Tool Management', 'value' => 70],
                    ['name' => 'Labor Compliance', 'value' => 78]
                ],
                'salary' => '$60,000 - $95,000',
                'growth' => 'Medium',
                'timeline' => '6 - 12 months',
                'openings' => '12,000+',
                'experience' => '1-4 years',
                'flexibility' => 'On-site',
                'icon' => 'Users',
                'roadmap' => [
                    ['title' => 'Talent Sourcing Systems & Boolean Queries', 'duration' => '2 months', 'desc' => 'Master custom boolean matching strings execution over corporate directories, structure applicant profiles tracking pipelines, and verify interview parameters.', 'tags' => ['SHRM', 'LinkedIn']],
                    ['title' => 'Employee Performance Operations & Feedback Frameworks', 'duration' => '2-3 months', 'desc' => 'Configure key performance indicators blueprints, run organizational goal alignment evaluations, and organize systematic skill calibration matrices.', 'tags' => ['BambooHR Tools', 'Glint Analytics']],
                    ['title' => 'Labor Compliance, Law Registries & Policy Structures', 'duration' => '3 months', 'desc' => 'Study corporate employment security rules, accessibility criteria, organizational risk profiles, and code enforcement files.', 'tags' => ['HRCI Handbooks', 'Legal Frameworks']],
                    ['title' => 'Corporate Culture Matrices & Interaction Indexing', 'duration' => '2 months', 'desc' => 'Administer structured worker sentiment trackers, implement resolution models, and track company engagement parameters.', 'tags' => ['CultureAmp', 'Workday Modules']]
                ],
                'courses' => [
                    ['title' => 'Human Resources Associate Certificate', 'platform' => 'Coursera', 'rating' => '4.7', 'cost' => 'Free'],
                    ['title' => 'Strategic Human Resource Operations Frameworks', 'platform' => 'LinkedIn Learning', 'rating' => '4.6', 'cost' => 'Free'],
                    ['title' => 'People Analytics: Data-Driven Workforce Planning', 'platform' => 'Wharton / Coursera', 'rating' => '4.8', 'cost' => 'Free'],
                    ['title' => 'Enterprise Talent Management Sourcing Specialist', 'platform' => 'Udemy Academic', 'rating' => '4.5', 'cost' => 'Free']
                ]
            ],
            [
                'title' => 'Financial Analyst',
                'match' => '74% Match',
                'demand' => 'Medium Demand',
                'description' => 'Build systematic financial ledger equations and multi-statement cash projections to audit capital parameters and forecast investment returns.',
                'role_overview' => 'Design systematic financial forecasting equations and investment yield indices to model corporate growth variables, evaluate cash distributions, and compute portfolio valuations.',
                'skills' => [
                    ['name' => 'Financial Modeling', 'value' => 92],
                    ['name' => 'Corporate Valuation', 'value' => 85],
                    ['name' => 'Excel Macro Engines', 'value' => 90],
                    ['name' => 'Data Forecasting', 'value' => 82]
                ],
                'salary' => '$68,000 - $115,000',
                'growth' => 'Medium',
                'timeline' => '12 - 18 months',
                'openings' => '14,000+',
                'experience' => '2-5 years',
                'flexibility' => 'Hybrid',
                'icon' => 'DollarSign',
                'roadmap' => [
                    ['title' => 'Accounting Foundations & Multi-Statement Ledgering', 'duration' => '3 months', 'desc' => 'Master the integration mechanics behind dynamic corporate cash statements, balance files auditing protocols, and performance spreadsheets.', 'tags' => ['Investopedia', 'CFI']],
                    ['title' => 'Financial Valuation Systems & Dynamic Engineering', 'duration' => '3-4 months', 'desc' => 'Construct comprehensive Discounted Cash Flow models, analyze transactional valuations arrays, and configure automated sensitivity analysis dashboards.', 'tags' => ['Excel Macros', 'WallStreetPrep']],
                    ['title' => 'Capital Budgeting Analytics & Risk Optimization', 'duration' => '2-3 months', 'desc' => 'Examine Internal Rate of Return vectors, evaluate net present value metrics, map asset depreciation lines, and audit capital variances.', 'tags' => ['Financial Formulas', 'CFA Guides']],
                    ['title' => 'Macro Trend Data Ingestion & Analytics Portfolios', 'duration' => '3 months', 'desc' => 'Track interest index fluctuations, calibrate inflation modeling sheets, track target microeconomics telemetry, and assemble summary investment brief cards.', 'tags' => ['Bloomberg Terminal', 'FactSet Tools']]
                ],
                'courses' => [
                    ['title' => 'Introduction to Corporate Finance', 'platform' => 'Coursera', 'rating' => '4.8', 'cost' => 'Free'],
                    ['title' => 'Advanced Financial Modeling and Valuation Methods', 'platform' => 'CFI Academy', 'rating' => '4.9', 'cost' => 'Free'],
                    ['title' => 'Financial Markets and Investment Security Analysis', 'platform' => 'Yale University / edX', 'rating' => '4.7', 'cost' => 'Free'],
                    ['title' => 'Python for Finance and Algorithmic Trading Systems', 'platform' => 'Udemy Premium', 'rating' => '4.6', 'cost' => 'Free']
                ]
            ],
            [
                'title' => 'Research Scientist',
                'match' => '81% Match',
                'demand' => 'Medium Demand',
                'description' => 'Design rigorous experimental loops and mathematical hypothesis arrays to isolate structural variances and publish industrial innovations.',
                'role_overview' => 'Formulate rigorous, empirical research methodologies and experimental pipelines to test multi-variable hypotheses and engineer industrial technological innovations.',
                'skills' => [
                    ['name' => 'Data Analytics', 'value' => 85],
                    ['name' => 'Statistical Modeling', 'value' => 90],
                    ['name' => 'Technical Writing', 'value' => 95],
                    ['name' => 'Hypothesis Design', 'value' => 88]
                ],
                'salary' => '$70,000 - $120,000',
                'growth' => 'Medium',
                'timeline' => '24 - 36 months',
                'openings' => '8,000+',
                'experience' => '3-6 years',
                'flexibility' => 'On-site Lab',
                'icon' => 'SearchIcon',
                'roadmap' => [
                    ['title' => 'Advanced Academic Methods & Double-Blind Frameworks', 'duration' => '6 months', 'desc' => 'Formulate systematic analytical checks, handle complex control population samples data, and review statistical validations rules metrics.', 'tags' => ['Nature', 'MIT OpenCourseWare']],
                    ['title' => 'Data Collection Procedures & Instrument Configurations', 'duration' => '4-5 months', 'desc' => 'Integrate specialized physical diagnostic arrays pipelines, write sensor monitoring scripts logs, and store structured multi-variant data blocks cleanly.', 'tags' => ['Lab Equipment', 'MATLAB Analysis']],
                    ['title' => 'Technical Drafting & Proprietary Patent Structuring', 'duration' => '4 months', 'desc' => 'Compose comprehensive research publications layouts, design structural legal asset patent formulations records, and compile open replication guidelines logs.', 'tags' => ['IEEE Formats', 'WIPO Standards']],
                    ['title' => 'Grant Ingestion Protocols & Program Coordination', 'duration' => '5-6 months', 'desc' => 'Assemble precise analytical funding packets, layout milestone expectations trajectories, audit institutional compliance records, and direct multi-stage asset distributions.', 'tags' => ['Research Portals', 'Academic Frameworks']]
                ],
                'courses' => [
                    ['title' => 'Understanding Research Methods', 'platform' => 'Coursera', 'rating' => '4.6', 'cost' => 'Free'],
                    ['title' => 'Biostatistics and Empirical Data Processing Systems', 'platform' => 'Harvard / edX', 'rating' => '4.8', 'cost' => 'Free'],
                    ['title' => 'Technical Writing and Scientific Documentation Formats', 'platform' => 'Stanford Online', 'rating' => '4.7', 'cost' => 'Free'],
                    ['title' => 'Data Analysis and Modeling Foundations for Scientists', 'platform' => 'MIT Web', 'rating' => '4.9', 'cost' => 'Free']
                ]
            ]
        ];

        foreach ($careers as $career) {
            CareerDetails::create($career);
        }
    }
}