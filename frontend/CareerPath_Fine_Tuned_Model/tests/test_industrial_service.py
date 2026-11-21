import unittest
import sys
import os
import time
import json

CAREER_ADVISOR_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
if CAREER_ADVISOR_DIR not in sys.path:
    sys.path.insert(0, CAREER_ADVISOR_DIR)

from model import CareerAdvisorModel, StudentInput
from app import app, PATHWAY_TO_CAREER, predict_for_backend, recommend_career

class IndustrialCareerAdvisorTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.model = CareerAdvisorModel(CAREER_ADVISOR_DIR)

    def test_01_feature_vector_dimension(self):
        """Verifies exact 49-feature high-discrimination matrix."""
        self.assertEqual(len(self.model.feature_names), 49)

    def test_02_all_12_pathways_registered(self):
        """Verifies all 12 specialized career pathways are supported."""
        self.assertEqual(len(self.model.class_names), 12)
        for pathway in self.model.class_names:
            self.assertIn(pathway, PATHWAY_TO_CAREER)

    def test_03_skill_gap_analysis_generation(self):
        """Verifies skill gap analysis correctly separates acquired vs missing skills."""
        student = StudentInput(grade=3.8, skills=["programming"], behaviors=["logical"])
        res = self.model.predict_recommendations(student)
        top_rec = res["recommendations"][0]
        
        self.assertIn("skill_gap_analysis", top_rec)
        gaps = top_rec["skill_gap_analysis"]
        self.assertIn("Programming", gaps["verified_competencies"])
        self.assertTrue(len(gaps["target_skill_gaps"]) > 0)
        self.assertIn("fit_breakdown", top_rec)
        self.assertIn("recommended_action_steps", top_rec)

    def test_04_calibrated_match_score(self):
        """Verifies realistic psychometric calibration (scores capped at <= 95%)."""
        student = StudentInput(grade=4.0, skills=["programming", "math", "critical thinking"], behaviors=["logical", "highly motivated"])
        res = self.model.predict_recommendations(student)
        top_rec = res["recommendations"][0]
        
        raw_pct = float(top_rec["confidence_percentage"].replace("%", ""))
        self.assertLessEqual(raw_pct, 95.0)
        self.assertGreaterEqual(raw_pct, 80.0)

    def test_05_predict_backend_integration(self):
        """Verifies Laravel payload adapter schema compatibility."""
        payload = {
            "profile": {"cgpa": "3.85", "core_skills": "programming, problem solving"},
            "answers": {"1": ["coding", "software architecture"]}
        }
        res = predict_for_backend(payload)
        self.assertEqual(res["status"], "success")
        self.assertIn("recommendations", res)
        top = res["recommendations"][0]
        self.assertIn("career_id", top)
        self.assertIn("title", top)
        self.assertIn("skill_gap_analysis", top)
        self.assertIn("fit_breakdown", top)
        self.assertIn("recommended_action_steps", top)

    def test_06_in_memory_cache_performance(self):
        """Verifies sub-millisecond retrieval on cached queries."""
        student = StudentInput(grade=3.5, skills=["programming", "problem solving"], behaviors=["logical"])
        _ = self.model.predict_recommendations(student)
        
        t0 = time.perf_counter()
        for _ in range(50):
            _ = self.model.predict_recommendations(student)
        elapsed_per_req_ms = ((time.perf_counter() - t0) / 50) * 1000
        
        self.assertLess(elapsed_per_req_ms, 1.0) # Sub-millisecond target

if __name__ == "__main__":
    unittest.main()
