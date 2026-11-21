# CareerPath AI — Fine-Tuned Career Advisor Model

Clean, standalone package containing the fine-tuned Random Forest + TreeSHAP Career Advisor model, FastAPI inference service, training pipeline, dataset, and verification tests.

## Contents

- `model.py` — `CareerAdvisorModel` inference engine, 40-feature engineering vector, TreeSHAP XAI attribution, skill-gap analysis, and domain-aligned calibration.
- `app.py` — FastAPI service (`/predict`, `/api/recommend`, `/healthz`, `/readyz`, `/metrics`, `/docs`) with quiz skill/behavior/grade/domain extraction.
- `cli_advisor.py` — Interactive CLI inference script.
- `train_finetuned_model.py` — Fine-tuned training script generating the `.pkl` artifacts from `FYP CLEAN DATA.xlsx`.
- `career_advisor_rf_model.pkl`, `career_feature_names.pkl`, `career_label_encoder.pkl`, `career_hierarchy.pkl`, `all_skills.pkl`, `all_behaviors.pkl`, `career_streamlit_bundle.pkl` — Trained model artifacts and encoders.
- `compute_confusion_matrix.py`, `plot_confusion_matrix.py` — Evaluation and confusion matrix utilities.
- `verify_model_health.py`, `test_all_12_pathways_in_app.py`, `tests/` — Verification and metric evaluation suites.

## Quick Start

```bash
pip install -r requirements.txt

# 1. Standalone model test
python model.py

# 2. Start FastAPI service
python -m uvicorn app:app --host 127.0.0.1 --port 8010

# 3. Verify live 12-pathway predictions
python test_all_12_pathways_in_app.py
python verify_model_health.py
```
