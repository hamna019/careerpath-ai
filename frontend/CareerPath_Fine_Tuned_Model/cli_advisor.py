"""
================================================================================
CareerAdvisor AI - Interactive Terminal Testing Tool (No Frontend Needed)
================================================================================
Run this tool directly in your terminal to test the 98% fine-tuned AI model!
Usage:
    py cli_advisor.py
================================================================================
"""

import sys
import os
from model import CareerAdvisorModel, StudentInput

def clear_screen():
    os.system('cls' if os.name == 'nt' else 'clear')

def main():
    print("=" * 70)
    print("  AI CAREER ADVISOR - DIRECT MODEL RUNNER (NO FRONTEND REQUIRED)")
    print("  Model Accuracy Tier: 98.18% Production (12 Career Pathways)")
    print("=" * 70)
    print("Loading AI model artifacts...")
    
    advisor = CareerAdvisorModel()
    print("Model loaded successfully!\n")
    
    sample_profiles = {
        "1": {
            "name": "AI / ML Researcher",
            "grade": 3.9,
            "skills": ["programming", "math", "critical thinking"],
            "behaviors": ["logical", "highly motivated"]
        },
        "2": {
            "name": "Software Engineer",
            "grade": 3.4,
            "skills": ["programming", "problem solving", "time management"],
            "behaviors": ["logical", "detail oriented"]
        },
        "3": {
            "name": "Business / Startup Founder",
            "grade": 3.2,
            "skills": ["leadership", "communication", "time management"],
            "behaviors": ["extrovert", "highly motivated"]
        },
        "4": {
            "name": "Cybersecurity & Cloud Analyst",
            "grade": 3.5,
            "skills": ["programming", "critical thinking", "problem solving"],
            "behaviors": ["detail oriented", "calm"]
        },
        "5": {
            "name": "Creative Designer / UI-UX",
            "grade": 3.3,
            "skills": ["problem solving", "communication", "adaptability"],
            "behaviors": ["calm", "introvert"]
        }
    }
    
    while True:
        print("\nChoose an option:")
        print("  [1-5] Run quick pre-set student profile")
        print("  [C]   Custom student profile (interactive input)")
        print("  [Q]   Quit")
        
        choice = input("\nEnter choice (1-5, C, or Q): ").strip().upper()
        
        if choice == 'Q':
            print("\nExiting. Goodbye!")
            break
            
        student_data = None
        
        if choice in sample_profiles:
            p = sample_profiles[choice]
            print(f"\n---> Running Profile: {p['name']}")
            student_data = StudentInput(
                grade=p["grade"],
                skills=p["skills"],
                behaviors=p["behaviors"]
            )
        elif choice == 'C':
            print("\n--- Enter Custom Student Details ---")
            try:
                grade_str = input("Enter GPA / Grade (e.g., 3.5): ").strip()
                grade = float(grade_str) if grade_str else 3.0
            except ValueError:
                grade = 3.0
                
            skills_input = input("Enter Skills separated by commas (e.g. programming, math, leadership): ").strip()
            skills_list = [s.strip().lower() for s in skills_input.split(",") if s.strip()]
            
            behaviors_input = input("Enter Behaviors (e.g. logical, extrovert, detail oriented, calm): ").strip()
            behaviors_list = [b.strip().lower() for b in behaviors_input.split(",") if b.strip()]
            
            student_data = StudentInput(
                grade=grade,
                skills=skills_list,
                behaviors=behaviors_list
            )
        else:
            print("Invalid option. Please try again.")
            continue
            
        print("\n[AI Inference] Analyzing profile across 12 disciplines & computing SHAP feature attributions...")
        result = advisor.predict_recommendations(student_data)
        
        print("\n" + "=" * 70)
        print("                      AI CAREER RECOMMENDATIONS")
        print("=" * 70)
        print(f"Model Tier       : {result['model_accuracy_tier']}")
        print(f"Profile Assessed : GPA {result['student_profile']['grade']}, Skills: {result['student_profile']['skills']}")
        print(f"Uncertainty      : {result['model_telemetry']['uncertainty'].upper()} (Entropy: {result['model_telemetry']['entropy']})")
        print("-" * 70)
        
        for rec in result["recommendations"]:
            print(f"\nRANK #{rec['rank']} -> {rec['field'].upper()}")
            print(f"Confidence Match : {rec['confidence_percentage']}")
            print(f"Driving Factors  : {', '.join(rec['key_factors'])}")
            print(f"AI Explanation   : {rec['explanation_paragraph']}")
            print(f"Career Ladder    : {' -> '.join(rec['progression_ladder'][:5])}...")
            
        print("\n" + "=" * 70)

if __name__ == "__main__":
    main()
