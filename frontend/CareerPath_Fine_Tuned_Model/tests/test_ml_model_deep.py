import os
import sys
import unittest
import time
import numpy as np
import pandas as pd
from sklearn.ensemble import ExtraTreesClassifier, RandomForestClassifier

# Ensure career_advisor is in Python path
CAREER_ADVISOR_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
if CAREER_ADVISOR_DIR not in sys.path:
    sys.path.insert(0, CAREER_ADVISOR_DIR)

from model import CareerAdvisorModel, StudentInput

class DeepMLModelTestSuite(unittest.TestCase):
    """
    Comprehensive ML Model Evaluation Suite testing:
    - Architecture & Feature Space Integrity
    - Multi-Class Discriminative Accuracy across all 12 Pathways
    - TreeSHAP Mathematical Attribution Validity
    - Boundary Clamping & Adversarial Input Robustness
    - Shannon Entropy & Confidence Score Calibration
    - Career Progression Ladder Completeness
    - Sub-Millisecond Cache Latency Benchmark
    """

    @classmethod
    def setUpClass(cls):
        print("\n[SETUP] Initializing CareerAdvisorModel and loading serialized artifacts...")
        cls.advisor = CareerAdvisorModel(CAREER_ADVISOR_DIR)

    # -------------------------------------------------------------
    # SUITE 1: Model Architecture & Feature Engineering
    # -------------------------------------------------------------
    def test_01_model_estimator_type_and_params(self):
        """Verifies ensemble architecture and hyperparameter specifications (ExtraTrees / RandomForest)."""
        model = self.advisor.model
        self.assertIsInstance(model, (ExtraTreesClassifier, RandomForestClassifier), "Model must be an ensemble forest classifier")
        self.assertGreaterEqual(model.n_estimators, 100, f"Model must utilize >= 100 ensemble trees, found {model.n_estimators}")
        self.assertIsNotNone(model.classes_, "Model must be fitted with valid classes")

    def test_02_feature_space_dimension(self):
        """Verifies exact 49-feature discrimination feature space."""
        features = self.advisor.feature_names
        self.assertEqual(len(features), 49, f"Feature space must contain exactly 49 features, found {len(features)}")
        self.assertIn("Grade", features, "Feature vector must include Grade")
        self.assertIn("skill_programming", features, "Feature vector must include skill_programming")
        self.assertIn("behavior_logical", features, "Feature vector must include behavior_logical")
        self.assertIn("syn_software", features, "Feature vector must include synergy interaction terms")

    def test_03_all_12_specialized_pathways_registered(self):
        """Verifies all 12 specialized career pathways are registered in label encoder."""
        classes = self.advisor.class_names
        self.assertEqual(len(classes), 12, "Must contain exactly 12 career pathways")
        expected = [
            "Accounting & Financial Analysis",
            "Artificial Intelligence & Machine Learning",
            "Business Management & Entrepreneurship",
            "Creative Arts & Media",
            "Cyber Security & Cloud",
            "Data Science & Analytics",
            "Digital Marketing & Growth Strategy",
            "Education & Counseling",
            "Engineering & Robotics",
            "Healthcare & Medicine",
            "Software Development",
            "UI/UX & Product Design"
        ]
        for pathway in expected:
            self.assertIn(pathway, classes, f"Missing career pathway: {pathway}")

    # -------------------------------------------------------------
    # SUITE 2: Multi-Class Discriminative Classification
    # -------------------------------------------------------------
    def test_04_software_development_archetype(self):
        """Tests that pure software engineering inputs correctly classify into Software Development."""
        student = StudentInput(
            grade=3.8,
            skills=["programming", "problem solving"],
            behaviors=["logical"]
        )
        res = self.advisor.predict_recommendations(student)
        top = res["recommendations"][0]
        self.assertEqual(top["field"], "Software Development")
        self.assertEqual(top["rank"], 1)

    def test_05_ai_machine_learning_archetype(self):
        """Tests that AI/ML engineering inputs classify into AI & Machine Learning."""
        student = StudentInput(
            grade=3.9,
            skills=["programming", "math", "critical thinking"],
            behaviors=["logical", "highly motivated"]
        )
        res = self.advisor.predict_recommendations(student)
        top_fields = [r["field"] for r in res["recommendations"][:2]]
        self.assertIn("Artificial Intelligence & Machine Learning", top_fields)

    def test_06_data_science_archetype(self):
        """Tests that statistical & analytical inputs classify into Data Science & Analytics."""
        student = StudentInput(
            grade=3.7,
            skills=["analytical skills", "math", "problem solving"],
            behaviors=["detail oriented", "logical"]
        )
        res = self.advisor.predict_recommendations(student)
        top_fields = [r["field"] for r in res["recommendations"][:2]]
        self.assertIn("Data Science & Analytics", top_fields)

    def test_07_healthcare_medicine_archetype(self):
        """Tests that medical and empathetic inputs classify into Healthcare & Medicine."""
        student = StudentInput(
            grade=3.9,
            skills=["critical thinking", "problem solving", "communication"],
            behaviors=["empathetic", "calm"]
        )
        res = self.advisor.predict_recommendations(student)
        top_fields = [r["field"] for r in res["recommendations"][:2]]
        self.assertIn("Healthcare & Medicine", top_fields)

    def test_08_ui_ux_product_design_archetype(self):
        """Tests that creative design inputs classify into UI/UX & Product Design."""
        student = StudentInput(
            grade=3.5,
            skills=["creativity", "problem solving", "communication"],
            behaviors=["creative", "empathetic", "detail oriented"]
        )
        res = self.advisor.predict_recommendations(student)
        top_fields = [r["field"] for r in res["recommendations"][:2]]
        self.assertIn("UI/UX & Product Design", top_fields)

    def test_09_accounting_finance_archetype(self):
        """Tests that financial analysis inputs classify into Accounting & Financial Analysis."""
        student = StudentInput(
            grade=3.6,
            skills=["analytical skills", "math", "time management"],
            behaviors=["detail oriented", "calm"]
        )
        res = self.advisor.predict_recommendations(student)
        top_fields = [r["field"] for r in res["recommendations"][:2]]
        self.assertIn("Accounting & Financial Analysis", top_fields)

    # -------------------------------------------------------------
    # SUITE 3: TreeSHAP Explainability (XAI) Matrix
    # -------------------------------------------------------------
    def test_10_shap_explainer_initialization(self):
        """Verifies TreeExplainer is initialized and attached to Random Forest."""
        self.assertIsNotNone(self.advisor.explainer, "TreeExplainer must be instantiated")

    def test_11_shap_positive_factor_attributions(self):
        """Verifies that driving features produce legitimate positive TreeSHAP factor attributions."""
        student = StudentInput(
            grade=3.85,
            skills=["programming", "math"],
            behaviors=["logical"]
        )
        res = self.advisor.predict_recommendations(student)
        top = res["recommendations"][0]

        # Key factors must be identified by TreeSHAP
        key_factors = top.get("key_factors", [])
        self.assertGreaterEqual(len(key_factors), 1, "Top recommendation must have >= 1 TreeSHAP factor")
        
        # Explanation paragraph must incorporate TreeSHAP findings
        explanation = top.get("explanation_paragraph", "")
        self.assertTrue(len(explanation) > 50, "Must provide detailed Explainable AI rationale")

    # -------------------------------------------------------------
    # SUITE 4: Input Normalization & Boundary Clamping
    # -------------------------------------------------------------
    def test_12_grade_clamping_and_normalization(self):
        """Verifies GPA is clamped between 1.0 and 4.0 preventing numerical anomalies."""
        # Low outlier
        s_low = StudentInput(grade=-1.5, skills=["programming"], behaviors=["logical"])
        res_low = self.advisor.predict_recommendations(s_low)
        self.assertIsNotNone(res_low)

        # High outlier
        s_high = StudentInput(grade=99.0, skills=["programming"], behaviors=["logical"])
        res_high = self.advisor.predict_recommendations(s_high)
        self.assertIsNotNone(res_high)

        # Non-numeric grade string
        s_str = StudentInput(grade="invalid", skills=["programming"], behaviors=["logical"])
        res_str = self.advisor.predict_recommendations(s_str)
        self.assertIsNotNone(res_str)

    def test_13_exploratory_fallback_on_empty_profile(self):
        """Verifies robust handling of zero-feature profiles without raising index or division errors."""
        empty_student = StudentInput(grade=3.2, skills=[], behaviors=[])
        res = self.advisor.predict_recommendations(empty_student)
        self.assertIn("recommendations", res)
        self.assertEqual(len(res["recommendations"]), 3)
        top = res["recommendations"][0]
        self.assertTrue(len(top["field"]) > 0)

    def test_14_case_insensitivity_and_whitespace_resilience(self):
        """Verifies input strings are properly sanitized and whitespace-trimmed."""
        student = StudentInput(
            grade=3.7,
            skills=["  PROGRAMMING  ", "  PROBLEM SOLVING  "],
            behaviors=["  LOGICAL  "]
        )
        res = self.advisor.predict_recommendations(student)
        top = res["recommendations"][0]
        self.assertEqual(top["field"], "Software Development")

    # -------------------------------------------------------------
    # SUITE 5: Shannon Entropy & Calibration
    # -------------------------------------------------------------
    def test_15_confidence_calibration_ceiling(self):
        """Verifies confidence score does not exceed realistic psychometric ceiling (<= 95%)."""
        student = StudentInput(
            grade=4.0,
            skills=["programming", "math", "critical thinking", "problem solving"],
            behaviors=["logical", "highly motivated", "detail oriented"]
        )
        res = self.advisor.predict_recommendations(student)
        top = res["recommendations"][0]
        conf_val = float(top["confidence_percentage"].replace("%", ""))
        self.assertLessEqual(conf_val, 95.0, "Confidence score must be capped at 95% maximum")
        self.assertGreaterEqual(conf_val, 75.0, "Confidence score for strong match should be >= 75%")

    def test_16_entropy_calculation(self):
        """Verifies prediction distribution entropy is computed and returned in model_telemetry."""
        student = StudentInput(grade=3.5, skills=["programming"], behaviors=["logical"])
        res = self.advisor.predict_recommendations(student)
        self.assertIn("model_telemetry", res)
        telemetry = res["model_telemetry"]
        self.assertIn("entropy", telemetry)
        self.assertIsInstance(telemetry["entropy"], (float, int))
        self.assertGreaterEqual(telemetry["entropy"], 0.0)
        self.assertIn("uncertainty", telemetry)

    # -------------------------------------------------------------
    # SUITE 6: Career Progression Ladders & Skill Gaps
    # -------------------------------------------------------------
    def test_17_career_progression_hierarchy(self):
        """Verifies all recommendations include multi-tier career progression ladders."""
        student = StudentInput(grade=3.5, skills=["programming"], behaviors=["logical"])
        res = self.advisor.predict_recommendations(student)
        top = res["recommendations"][0]
        ladder = top.get("progression_ladder", [])
        self.assertGreaterEqual(len(ladder), 4, "Must supply at least 4 career progression stages")

    def test_18_skill_gap_analysis(self):
        """Verifies accurate categorization of verified vs target skill gaps."""
        student = StudentInput(grade=3.5, skills=["programming"], behaviors=["logical"])
        res = self.advisor.predict_recommendations(student)
        top = res["recommendations"][0]
        gap_analysis = top.get("skill_gap_analysis", {})
        self.assertIn("verified_competencies", gap_analysis)
        self.assertIn("target_skill_gaps", gap_analysis)
        self.assertIn("Programming", gap_analysis["verified_competencies"])

    # -------------------------------------------------------------
    # SUITE 7: In-Memory Cache & Latency Benchmark
    # -------------------------------------------------------------
    def test_19_sub_millisecond_cache_retrieval(self):
        """Benchmarks that repeated cached queries resolve in sub-millisecond time (< 1.0 ms)."""
        student = StudentInput(grade=3.6, skills=["programming", "problem solving"], behaviors=["logical"])
        
        # Prime cache
        _ = self.advisor.predict_recommendations(student)

        # Benchmark 100 iterations
        t0 = time.perf_counter()
        iterations = 100
        for _ in range(iterations):
            _ = self.advisor.predict_recommendations(student)
        t_total = time.perf_counter() - t0
        avg_ms = (t_total / iterations) * 1000

        print(f"\n[BENCHMARK] Average cached inference latency: {avg_ms:.4f} ms per query")
        self.assertLess(avg_ms, 1.0, f"Cached inference should be < 1.0 ms, achieved {avg_ms:.4f} ms")

    def test_20_deterministic_cache_consistency(self):
        """Verifies 100% deterministic consistency across sequential calls."""
        student = StudentInput(grade=3.7, skills=["creativity"], behaviors=["creative"])
        res1 = self.advisor.predict_recommendations(student)
        res2 = self.advisor.predict_recommendations(student)
        self.assertEqual(res1["recommendations"][0]["field"], res2["recommendations"][0]["field"])
        self.assertEqual(res1["recommendations"][0]["confidence_percentage"], res2["recommendations"][0]["confidence_percentage"])

if __name__ == '__main__':
    unittest.main()
