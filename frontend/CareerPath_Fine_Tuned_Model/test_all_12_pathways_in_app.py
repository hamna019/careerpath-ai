import os
import sys
import requests
import json

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

FASTAPI_URL = "http://127.0.0.1:8010/predict"

ALL_12_TEST_SUITE = [
    {
        "domain": "Software Development",
        "expected_title": "Software Developer",
        "expected_id": 1,
        "skills": "Python, JavaScript, React, Problem Solving, Docker",
        "answers": ["Coding, building apps or exploring new technology", "Computer Science & IT"]
    },
    {
        "domain": "Artificial Intelligence & Machine Learning",
        "expected_title": "Machine Learning Engineer",
        "expected_id": 11,
        "skills": "Python, PyTorch, Math, Critical Thinking, Algorithms",
        "answers": ["Solving complex puzzles and debugging algorithmic logic", "Mathematics & Statistics", "Computer Science & IT"]
    },
    {
        "domain": "Data Science & Analytics",
        "expected_title": "Data Analyst",
        "expected_id": 2,
        "skills": "Analytical Skills, Math, Problem Solving, SQL, Statistics",
        "answers": ["Analyzing data, spotting trends, and finding business insights", "Mathematics & Statistics"]
    },
    {
        "domain": "Cyber Security & Cloud",
        "expected_title": "Cybersecurity Analyst",
        "expected_id": 12,
        "skills": "Critical Thinking, Problem Solving, Adaptability, Networking",
        "answers": ["Protecting networks, ethical hacking and security defense", "Computer Science & IT"]
    },
    {
        "domain": "Business Management & Entrepreneurship",
        "expected_title": "Entrepreneur / Startup Founder",
        "expected_id": 50,
        "skills": "Leadership, Communication, Teamwork, Strategy",
        "answers": ["Leading a team, pitching ideas or starting something of my own", "Business, Accounting & Economics"]
    },
    {
        "domain": "Digital Marketing & Growth Strategy",
        "expected_title": "Digital Marketing Specialist",
        "expected_id": 5,
        "skills": "Communication, Creativity, Adaptability, Teamwork",
        "answers": ["Social media strategy, online marketing campaigns, brand growth", "Languages, Literature & Communication", "Extrovert, energetic people person and confident presenter"]
    },
    {
        "domain": "Accounting & Financial Analysis",
        "expected_title": "Financial Analyst",
        "expected_id": 8,
        "skills": "Analytical Skills, Math, Time Management, Budgeting",
        "answers": ["Financial modeling, investment analysis and corporate budgets", "Business, Accounting & Economics"]
    },
    {
        "domain": "Healthcare & Medicine",
        "expected_title": "Physician (General Practitioner)",
        "expected_id": 23,
        "skills": "Critical Thinking, Problem Solving, Communication",
        "answers": ["Teaching, guiding or helping people through their problems", "Biology & Life Sciences"]
    },
    {
        "domain": "Engineering & Robotics",
        "expected_title": "Mechanical Engineer",
        "expected_id": 28,
        "skills": "Problem Solving, Math, Critical Thinking, CAD, Hardware",
        "answers": ["Building, repairing or assembling things with my hands", "Physics & Chemistry"]
    },
    {
        "domain": "UI/UX & Product Design",
        "expected_title": "UX Designer",
        "expected_id": 3,
        "skills": "Creativity, Problem Solving, Communication, Figma",
        "answers": ["Drawing, designing, writing stories or making music", "Art, Design & Media Studies"]
    },
    {
        "domain": "Creative Arts & Media",
        "expected_title": "Content Creator",
        "expected_id": 35,
        "skills": "Creativity, Communication, Adaptability, Video Editing",
        "answers": ["Drawing, designing, writing stories or making music", "Languages, Literature & Communication"]
    },
    {
        "domain": "Education & Counseling",
        "expected_title": "High School Teacher",
        "expected_id": 38,
        "skills": "Communication, Leadership, Adaptability, Mentoring",
        "answers": ["Teaching, guiding or helping people through their problems", "Psychology & Sociology"]
    }
]

def verify_all_12_pathways():
    print("=" * 80)
    print("🎯 COMPREHENSIVE 12-PATHWAY CAREER SUGGESTION ACCURACY TEST")
    print("=" * 80)

    passed = 0
    failed = 0

    print(f"{'#':<3} {'Target Pathway':<38} {'Suggested Career':<32} {'ID':<5} {'Score':<7} {'Status'}")
    print("-" * 92)

    for i, item in enumerate(ALL_12_TEST_SUITE, 1):
        payload = {
            "profile": {
                "cgpa": "3.80",
                "core_skills": item["skills"]
            },
            "answers": {str(idx + 1): [ans] for idx, ans in enumerate(item["answers"])}
        }

        try:
            res = requests.post(FASTAPI_URL, json=payload, timeout=5)
            if res.status_code != 200:
                print(f"{i:<3} {item['domain']:<38} HTTP ERROR {res.status_code}")
                failed += 1
                continue

            data = res.json()
            recs = data.get("recommendations", [])
            if not recs:
                print(f"{i:<3} {item['domain']:<38} NO RECOMMENDATIONS")
                failed += 1
                continue

            top = recs[0]
            top_title = top.get("title", "")
            top_id = top.get("career_id", 0)
            top_score = top.get("confidence_percentage", "0%")
            top_field = top.get("field", "")

            # Verify both title and ID
            is_correct = (top_title == item["expected_title"] and top_id == item["expected_id"])
            if is_correct:
                status = "✓ ACCURATE"
                passed += 1
            else:
                status = f"✕ EXPECTED {item['expected_title']} (#{item['expected_id']})"
                failed += 1

            print(f"{i:<3} {item['domain']:<38} {top_title:<32} {top_id:<5} {top_score:<7} {status}")

        except Exception as e:
            print(f"{i:<3} {item['domain']:<38} EXCEPTION: {e}")
            failed += 1

    print("-" * 92)
    print(f"Results: {passed} / {len(ALL_12_TEST_SUITE)} Pathways Suggested Accurately ({(passed / len(ALL_12_TEST_SUITE)) * 100:.1f}%)")
    print("=" * 80)

    return failed == 0

if __name__ == '__main__':
    ok = verify_all_12_pathways()
    sys.exit(0 if ok else 1)
