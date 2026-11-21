<?php

namespace App\Services;

use App\Models\CareerDetails;
use App\Models\QuizQuestion;
use Illuminate\Support\Collection;

class CareerMatcher
{
    private const QUESTION_WEIGHTS = ['high' => 3.0, 'medium' => 2.0, 'low' => 1.0];

    private const RIASEC = ['R', 'I', 'A', 'S', 'E', 'C'];

    private const RIASEC_LABELS = [
        'R' => 'Realistic (hands-on)',
        'I' => 'Investigative (analytical)',
        'A' => 'Artistic (creative)',
        'S' => 'Social (helping people)',
        'E' => 'Enterprising (leading & persuading)',
        'C' => 'Conventional (organizing & detail)',
    ];

    private const EDU_LEVELS = ['short' => 0, 'diploma' => 1, 'self' => 1, 'bachelor' => 2, 'master' => 3, 'long' => 4];

    private const DOMAIN_KEYWORDS = [
        'tech'        => ['software', 'developer', 'programming', 'coding', ' app', 'apps', 'website', 'web ', 'cloud', 'devops', 'it ', 'computer', 'ai ', 'artificial intelligence', 'machine learning', 'technology'],
        'data'        => ['data', 'analytics', 'statistic', 'machine learning', 'analyst'],
        'cyber'       => ['security', 'cyber', 'hacker', 'penetration'],
        'eng'         => ['engineer', 'engineering', 'mechanical', 'electrical', 'civil', 'construction'],
        'health'      => ['health', 'medical', 'medicine', 'doctor', 'nurse', 'patient', 'clinic', 'hospital', 'therapy', 'surgeon', 'physician'],
        'science'     => ['science', 'scientist', 'research', 'laboratory', 'lab ', 'physics', 'chemistry', 'biology'],
        'business'    => ['business', 'management', 'manager', 'sales', 'marketing', 'startup', 'entrepreneur', 'operations'],
        'finance'     => ['finance', 'financial', 'bank', 'accounting', 'accountant', 'investment', 'tax', 'audit'],
        'law'         => ['law', 'legal', 'lawyer', 'court', 'justice', 'compliance'],
        'gov'         => ['government', 'public service', 'policy', 'civil service', 'diplomat'],
        'edu'         => ['teacher', 'teaching', 'education', 'school', 'professor', 'tutor', 'training'],
        'design'      => ['design', 'designer', ' ux', ' ui ', 'graphic', 'interior', 'fashion'],
        'media'       => ['media', 'content', 'video', 'social media', 'journalis', 'writer', 'film', 'broadcast'],
        'arts'        => [' art ', ' arts', 'artist', 'music', 'acting', 'actor', 'creative', 'photograph'],
        'social'      => ['social', 'community', 'counsel', 'welfare', 'ngo'],
        'hospitality' => ['hotel', 'hospitality', 'chef', 'restaurant', 'tourism', 'travel', 'event'],
        'trades'      => ['technician', 'repair', 'mechanic', 'electrician', 'plumb', 'weld', 'install'],
        'transport'   => ['pilot', 'aviation', 'aircraft', 'airline', 'logistics', 'shipping', 'driver', 'marine'],
        'defense'     => ['military', 'army', 'police', 'defense', 'defence', 'security forces', 'firefight'],
        'sports'      => ['sport', 'athlete', 'fitness', 'coach', 'gym', 'trainer'],
        'agri'        => ['agri', 'farm', 'crop', 'livestock', 'food', 'animal'],
        'env'         => ['environment', 'climate', 'sustainab', 'renewable', 'energy', 'ecology', 'green'],
    ];

    private const DOMAIN_RIASEC = [
        'tech' => 'IRC', 'data' => 'ICR', 'cyber' => 'ICR', 'eng' => 'RIC', 'health' => 'ISR',
        'science' => 'IRC', 'business' => 'ECS', 'finance' => 'CEI', 'law' => 'EIS', 'gov' => 'ESC',
        'edu' => 'SAI', 'design' => 'AIR', 'media' => 'AES', 'arts' => 'AES', 'social' => 'SEA',
        'hospitality' => 'ESR', 'trades' => 'RCI', 'transport' => 'RCI', 'defense' => 'RES',
        'sports' => 'RSE', 'agri' => 'RIS', 'env' => 'IRS',
    ];

    private const REASON_TEMPLATES = [
        'interest'    => 'Your interest in "%s" closely matches the day-to-day work in this career.',
        'skill'       => 'Your strength in "%s" is one of the key abilities this role relies on.',
        'personality' => 'Your style, "%s", fits the way people succeed in this role.',
        'environment' => 'Your preference for "%s" matches what this career offers.',
        'education'   => 'Your background, "%s", gives you a solid starting point for this path.',
        'goal'        => 'Your ambition, "%s", aligns directly with where this career can take you.',
        'default'     => 'Your answer "%s" aligns well with this career.',
    ];

    public function match(array $answers): array
    {
        $student = $this->buildStudentProfile($answers);

        $scored = CareerDetails::all()
            ->map(fn (CareerDetails $career) => $this->scoreCareer($career, $student))
            ->sortByDesc('score')
            ->values();

        $picked = $this->pickRecommendations($scored);

        $topScore  = $picked[0]['score'] ?? 0;
        $topMatch  = (int) max(60, min(97, round(62 + 36 * $topScore)));

        $recommendations = [];
        foreach ($picked as $rank => $item) {
            $match = $rank === 0
                ? $topMatch
                : (int) max(50, round($topMatch * ($item['score'] / max($topScore, 0.0001))));

            $recommendations[] = [
                'rank'      => $rank + 1,
                'career_id' => $item['career']->id,
                'title'     => $item['career']->title,
                'match'     => $match,
                'source'    => 'rules',
                'reasons'   => $this->buildReasons($item, $student, $rank + 1),
            ];
        }

        return [
            'recommendations' => $recommendations,
            'profile'         => [
                'riasec'  => $this->topKeys($student['riasec'], 3),
                'domains' => $this->topKeys($student['domains'], 3),
            ],
        ];
    }

    public function explain(array $answers, array $ranked, string $source = 'ml'): array
    {
        $student = $this->buildStudentProfile($answers);
        $careers = CareerDetails::whereIn('id', array_column($ranked, 'career_id'))->get()->keyBy('id');

        $recommendations = [];
        foreach (array_values($ranked) as $i => $item) {
            $career = $careers->get($item['career_id']);
            if (!$career) {
                continue;
            }
            $scored = $this->scoreCareer($career, $student);
            $recommendations[] = [
                'rank'      => count($recommendations) + 1,
                'career_id' => $career->id,
                'title'     => $career->title,
                'match'     => (int) $item['match'],
                'source'    => $source,
                'reasons'   => $this->buildReasons($scored, $student, count($recommendations) + 1),
            ];
        }

        return $recommendations;
    }

    private const SUMMARY_LABELS = [
        'tech' => 'Software & Technology', 'data' => 'Data & Analytics', 'cyber' => 'Cybersecurity',
        'eng' => 'Engineering', 'health' => 'Healthcare & Medicine', 'science' => 'Science & Research',
        'business' => 'Business & Management', 'finance' => 'Finance & Accounting', 'law' => 'Law & Justice',
        'gov' => 'Government & Public Service', 'edu' => 'Education & Teaching', 'design' => 'Design',
        'media' => 'Media & Communication', 'arts' => 'Arts & Entertainment', 'social' => 'Social Work & Community',
        'hospitality' => 'Hospitality & Tourism', 'trades' => 'Skilled Trades', 'transport' => 'Aviation & Transport',
        'defense' => 'Defense & Emergency Services', 'sports' => 'Sports & Fitness', 'agri' => 'Agriculture & Food',
        'env' => 'Environment & Energy',
        'apt:numeric' => 'Working with numbers', 'apt:logical' => 'Logical problem solving', 'apt:verbal' => 'Writing & language',
        'apt:speaking' => 'Speaking & persuading', 'apt:visual' => 'Visual creativity', 'apt:mechanical' => 'Hands-on & mechanical',
        'apt:empathy' => 'Understanding people', 'apt:detail' => 'Attention to detail', 'apt:tech_high' => 'Advanced technology',
        'apt:tech_mid' => 'Learning new tools quickly', 'apt:tech_basic' => 'Everyday digital tools', 'apt:tech_low' => 'Practical, screen-free work',
        'style:solo' => 'Independent, focused work', 'style:team' => 'Close teamwork', 'style:lead' => 'Leading people',
        'style:people' => 'Meeting people & clients', 'style:mixed' => 'Balanced solo & team work', 'style:structured' => 'Clear structure',
        'style:crisis' => 'Calm under pressure',
        'value:income' => 'High income', 'value:security' => 'Job security', 'value:impact' => 'Making an impact',
        'value:creativity' => 'Creative freedom', 'value:status' => 'Recognition & status', 'value:balance' => 'Work-life balance',
        'value:learning' => 'Continuous learning', 'value:independence' => 'Independence',
        'env:office' => 'Office', 'env:remote' => 'Remote / from home', 'env:hospital' => 'Hospital or clinic', 'env:lab' => 'Laboratory',
        'env:outdoor' => 'Outdoors', 'env:studio' => 'Creative studio', 'env:campus' => 'School or campus', 'env:travel' => 'On the move / travel',
        'env:plant' => 'Site or factory', 'env:govt' => 'Government office',
    ];

    public function summarize(array $answers): array
    {
        $student = $this->buildStudentProfile($answers);
        $signals = $student['signals'];

        $rank = function (array $values, int $limit, callable $label): array {
            $values = array_filter($values, fn ($v) => $v > 0);
            $total = array_sum($values);
            arsort($values);

            return collect(array_slice($values, 0, $limit, true))
                ->map(fn ($v, $key) => ['label' => $label($key), 'percent' => (int) round($v / $total * 100)])
                ->values()
                ->all();
        };
        $group = fn (string $prefix) => array_filter($signals, fn ($k) => str_starts_with($k, $prefix), ARRAY_FILTER_USE_KEY);
        $name = fn ($key) => self::SUMMARY_LABELS[$key] ?? ucfirst((string) $key);

        $personality = $rank($student['riasec'], 3, fn ($letter) => self::RIASEC_LABELS[$letter]);

        return [
            'hollandCode' => implode('', array_map(fn ($p) => $p['label'][0], $personality)),
            'personality' => $personality,
            'interests'   => $rank(array_intersect_key($student['domains'], self::DOMAIN_RIASEC), 4, $name),
            'strengths'   => $rank($group('apt:'), 4, $name),
            'workStyle'   => $rank($group('style:'), 3, $name),
            'values'      => $rank($group('value:'), 3, $name),
            'workplace'   => $rank($group('env:'), 3, $name),
        ];
    }

    private function buildStudentProfile(array $answers): array
    {
        $questions = QuizQuestion::whereIn('id', array_keys($answers))->get()->keyBy('id');

        $riasec   = array_fill_keys(self::RIASEC, 0.0);
        $domains  = [];
        $signals  = [];
        $eduWill  = null;
        $grade    = null;
        $selected = [];

        foreach ($answers as $questionId => $chosen) {
            $question = $questions->get((int) $questionId);
            if (!$question || !is_array($chosen) || count($chosen) === 0) {
                continue;
            }

            $traitsMap = $this->optionTraitsOf($question);
            $weight    = self::QUESTION_WEIGHTS[strtolower((string) $question->weight)] ?? 2.0;
            $share     = $weight / sqrt(count($chosen));

            foreach ($chosen as $optionText) {
                $optionText = trim((string) $optionText);
                $tags = $traitsMap[$optionText] ?? $this->inferDomains($optionText);

                foreach ($tags as $tag) {
                    if (in_array($tag, self::RIASEC, true)) {
                        $riasec[$tag] += $share;
                    } elseif (str_starts_with($tag, 'edu:') && isset(self::EDU_LEVELS[substr($tag, 4)])) {
                        $eduWill = max($eduWill ?? 0, self::EDU_LEVELS[substr($tag, 4)]);
                    } elseif (str_starts_with($tag, 'grade:')) {
                        $grade = max($grade ?? 0, (int) substr($tag, 6));
                    } elseif (!str_contains($tag, ':')) {
                        $domains[$tag] = ($domains[$tag] ?? 0) + $share;
                    } else {
                        $signals[$tag] = ($signals[$tag] ?? 0) + $share;
                    }
                }

                $selected[] = [
                    'category' => (string) $question->category,
                    'text'     => $optionText,
                    'tags'     => $tags,
                    'share'    => $share,
                ];
            }
        }

        return compact('riasec', 'domains', 'signals', 'eduWill', 'grade', 'selected');
    }

    private function optionTraitsOf(QuizQuestion $question): array
    {
        $traits = $question->option_traits;
        if (is_string($traits)) {
            $traits = json_decode($traits, true);
        }

        return is_array($traits) ? $traits : [];
    }

    private function careerProfile(CareerDetails $career): array
    {
        $traits = is_array($career->traits) ? $career->traits : [];

        $domains = array_values(array_filter($traits['domains'] ?? []));
        if (!$domains) {
            $domains = $this->inferDomains($career->title . ' ' . $career->description);
        }
        if (!$domains) {
            $domains = ['business'];
        }

        $riasec = array_values(array_filter($traits['riasec'] ?? []));
        if (!$riasec) {
            $riasec = str_split(self::DOMAIN_RIASEC[$domains[0]] ?? 'ISE');
        }

        $edu = $traits['edu'] ?? $this->inferEdu($career);

        return ['domains' => $domains, 'riasec' => $riasec, 'edu' => $edu];
    }

    private function scoreCareer(CareerDetails $career, array $student): array
    {
        $profile = $this->careerProfile($career);

        $domainWeights = [];
        foreach ($profile['domains'] as $i => $domain) {
            $domainWeights[$domain] = $i === 0 ? 3.0 : 2.0;
        }
        $riasecWeights = [];
        foreach (array_slice($profile['riasec'], 0, 3) as $i => $letter) {
            $riasecWeights[$letter] = [3.0, 2.0, 1.0][$i];
        }

        $maxDomain = max($student['domains'] ?: [0]) ?: 1;
        $maxRiasec = max($student['riasec']) ?: 1;

        $domainFit = 0.0;
        foreach ($domainWeights as $domain => $w) {
            $domainFit += $w * (($student['domains'][$domain] ?? 0) / $maxDomain);
        }
        $domainFit /= array_sum($domainWeights);

        $riasecFit = 0.0;
        foreach ($riasecWeights as $letter => $w) {
            $riasecFit += $w * (($student['riasec'][$letter] ?? 0) / $maxRiasec);
        }
        $riasecFit /= array_sum($riasecWeights) ?: 1;

        $eduFit = 1.0;
        $required = self::EDU_LEVELS[$profile['edu']] ?? 2;
        if ($student['eduWill'] !== null && $student['eduWill'] < $required) {
            $eduFit = max(0.4, 1 - 0.2 * ($required - $student['eduWill']));
        } elseif ($student['eduWill'] !== null && $student['eduWill'] > $required) {
            $eduFit = max(0.7, 1 - 0.08 * ($student['eduWill'] - $required));
        }
        if ($student['grade'] !== null && $required >= 4 && $student['grade'] <= 1) {
            $eduFit *= 0.9;
        }

        $score = (0.6 * $domainFit + 0.4 * $riasecFit) * (0.7 + 0.3 * $eduFit);

        return [
            'career'        => $career,
            'profile'       => $profile,
            'domainWeights' => $domainWeights,
            'riasecWeights' => $riasecWeights,
            'eduFit'        => $eduFit,
            'score'         => round($score, 6),
        ];
    }

    private function pickRecommendations(Collection $scored): array
    {
        if ($scored->isEmpty()) {
            return [];
        }

        $top    = $scored->first();
        $picked = [$top];
        $usedDomains    = [$top['profile']['domains'][0]];
        $usedSignatures = [$this->signature($top)];

        foreach ($scored->slice(1) as $item) {
            if (count($picked) >= 3) {
                break;
            }
            $ratio = $item['score'] / max($top['score'], 0.0001);
            if ($ratio < 0.88) {
                break;
            }

            $signature = $this->signature($item);
            if (in_array($signature, $usedSignatures, true)) {
                continue;
            }

            $primary = $item['profile']['domains'][0];
            if (!in_array($primary, $usedDomains, true) || $ratio >= 0.97) {
                $picked[] = $item;
                $usedDomains[]    = $primary;
                $usedSignatures[] = $signature;
            }
        }

        return $picked;
    }

    private function signature(array $item): string
    {
        return $item['profile']['domains'][0] . ':' . implode('', array_slice($item['profile']['riasec'], 0, 2));
    }

    private function buildReasons(array $item, array $student, int $rank = 1): array
    {
        $career  = $item['career'];
        $reasons = [];

        $contributions = [];
        foreach ($student['selected'] as $option) {
            $value = 0.0;
            foreach ($option['tags'] as $tag) {
                $value += ($item['domainWeights'][$tag] ?? 0) + 0.6 * ($item['riasecWeights'][$tag] ?? 0);
            }
            if ($value > 0) {
                $key = $option['text'];
                $contributions[$key] = [
                    'value'    => ($contributions[$key]['value'] ?? 0) + $value * $option['share'],
                    'category' => $option['category'],
                    'text'     => $option['text'],
                ];
            }
        }
        usort($contributions, fn ($a, $b) => $b['value'] <=> $a['value']);

        $usedCategories = [];
        foreach ($contributions as $c) {
            if (count($reasons) >= 7) {
                break;
            }
            $type = $this->reasonType($c['category']);
            if (($usedCategories[$type] ?? 0) >= 2) {
                continue;
            }
            $usedCategories[$type] = ($usedCategories[$type] ?? 0) + 1;
            $reasons[] = sprintf(self::REASON_TEMPLATES[$type], $c['text']);
        }

        $topThemes = array_values(array_intersect($this->topKeys($student['riasec'], 2), array_keys($item['riasecWeights'])));
        if ($topThemes) {
            $labels = array_map(fn ($l) => self::RIASEC_LABELS[$l], $topThemes);
            $reasons[] = count($labels) > 1
                ? "Your strongest interest themes are {$labels[0]} and {$labels[1]}, the same themes at the heart of this career."
                : "Your strongest interest theme is {$labels[0]}, which sits at the heart of this career.";
        }

        $skills = collect($career->skills ?? [])->map(fn ($s) => is_array($s) ? ($s['name'] ?? null) : $s)->filter()->take(3)->values();
        if ($skills->count() >= 2) {
            $list = $skills->count() === 3 ? "{$skills[0]}, {$skills[1]} and {$skills[2]}" : "{$skills[0]} and {$skills[1]}";
            $reasons[] = "The key skills in this career, {$list}, build on the strengths and interests you showed in your answers.";
        }

        if ($student['eduWill'] !== null && $career->timeline) {
            $reasons[] = $item['eduFit'] >= 1.0
                ? "You are ready to invest the time this path typically needs ({$career->timeline} of study and training)."
                : "This path usually takes {$career->timeline} of study and training, so plan for a longer journey than you indicated.";
        }

        $total = CareerDetails::count();
        $reasons[] = $rank === 1
            ? "Our AI model ranked this as your #1 match out of {$total} careers after analysing your complete answer pattern."
            : "Our AI model ranked this as your #{$rank} match out of {$total} careers, a strong alternative worth exploring.";

        if ($career->demand && $career->growth) {
            $reasons[] = sprintf(
                'It is a %s career with %s growth%s.',
                mb_strtolower(str_replace(' Demand', '-demand', $career->demand)),
                mb_strtolower($career->growth),
                $career->openings ? " and {$career->openings} openings" : ''
            );
        }
        if ($career->salary) {
            $reasons[] = "Typical salaries range from {$career->salary}, with strong room to grow as you gain experience.";
        }

        $firstStep = collect($career->roadmap ?? [])->first();
        if (is_array($firstStep) && !empty($firstStep['title'])) {
            $reasons[] = "Your personalised roadmap starts with \"{$firstStep['title']}\" and guides you step by step into this career.";
        }
        $course = collect($career->courses ?? [])->first(fn ($c) => is_array($c) && !empty($c['title']));
        if ($course) {
            $platform = !empty($course['platform']) ? " on {$course['platform']}" : '';
            $reasons[] = "You can start learning today with \"{$course['title']}\"{$platform}.";
        }

        if ($career->flexibility) {
            $flex = mb_strtolower($career->flexibility);
            $wantsRemote = ($student['signals']['env:remote'] ?? 0) > 0;
            $reasons[] = $wantsRemote && (str_contains($flex, 'remote') || str_contains($flex, 'hybrid'))
                ? "Roles in this field are often {$flex}, which matches your preference for flexible, remote-friendly work."
                : "Most roles in this field are {$flex}, so you already know what your day-to-day work setting will look like.";
        }

        if ($career->experience) {
            $reasons[] = "Entry requirement: {$career->experience}, which you can start planning for right away.";
        }

        return array_slice(array_values(array_unique($reasons)), 0, 15);
    }

    private function reasonType(string $category): string
    {
        $c = mb_strtolower($category);

        return match (true) {
            str_contains($c, 'interest') || str_contains($c, 'passion') => 'interest',
            str_contains($c, 'skill') || str_contains($c, 'aptitude')   => 'skill',
            str_contains($c, 'personality') || str_contains($c, 'style') => 'personality',
            str_contains($c, 'environment') || str_contains($c, 'value') => 'environment',
            str_contains($c, 'education') || str_contains($c, 'learning') => 'education',
            str_contains($c, 'goal') || str_contains($c, 'aspiration')  => 'goal',
            default => 'default',
        };
    }

    private function inferDomains(string $text): array
    {
        $text = ' ' . mb_strtolower($text) . ' ';
        $hits = [];
        foreach (self::DOMAIN_KEYWORDS as $domain => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    $hits[$domain] = ($hits[$domain] ?? 0) + 1;
                }
            }
        }
        arsort($hits);

        return array_slice(array_keys($hits), 0, 3);
    }

    private function inferEdu(CareerDetails $career): string
    {
        $text = mb_strtolower($career->experience . ' ' . $career->timeline);

        return match (true) {
            str_contains($text, 'md') || str_contains($text, 'phd') || str_contains($text, 'residency') => 'long',
            str_contains($text, 'master')                                    => 'master',
            str_contains($text, 'degree')                                    => 'bachelor',
            str_contains($text, 'license') || str_contains($text, 'certif')  => 'diploma',
            default                                                          => 'bachelor',
        };
    }

    private function topKeys(array $values, int $n): array
    {
        $values = array_filter($values, fn ($v) => $v > 0);
        arsort($values);

        return array_slice(array_keys($values), 0, $n);
    }
}
