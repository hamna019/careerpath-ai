import os
import joblib
import pandas as pd
import numpy as np
from sklearn.metrics import confusion_matrix, classification_report, accuracy_score
from sklearn.model_selection import train_test_split
import json

# 1. Load exported model artifacts
rf_model = joblib.load("career_advisor_rf_model.pkl")
le = joblib.load("career_label_encoder.pkl")
feature_cols = joblib.load("career_feature_names.pkl")
SKILLS_VOCAB = joblib.load("all_skills.pkl")
BEHAVIORS_VOCAB = joblib.load("all_behaviors.pkl")
classes = list(le.classes_)

CAREER_MAP = {
    'Digital Marketing': 'Business',
    'Cloud Computing': 'Cyber Security',
    'Web Development': 'Software',
    'AI & Machine Learning': 'AI',
    'Mobile App Development': 'Software',
    'Networking': 'Cyber Security',
    'Software Development': 'Software'
}

# 2. Re-create the dataset as in train_model.py
dataset_path = 'FYP CLEAN DATA.xlsx'
df_raw = pd.read_excel(dataset_path, skiprows=3).dropna(subset=['Enroll No']).copy()
df_raw['CleanCareer'] = df_raw['Intrest'].replace(CAREER_MAP)

real_records = []
for _, row in df_raw.iterrows():
    raw_grade = float(row['Grade'])
    grade = round(float(np.clip(raw_grade, 1.0, 4.0)), 2)
    career = str(row['CleanCareer']).strip()
    sk_raw = str(row['Skills']).lower().strip()
    bh_raw = str(row['Psychometric Behavior']).lower().strip()
    
    if sk_raw in ['introvert', 'leader', 'logical']:
        bh_raw += ' ' + sk_raw
        sk_raw = ''
    elif sk_raw == 'leader':
        sk_raw = 'leadership'
        
    rec = {'Grade': grade, 'Target': career}
    for s in SKILLS_VOCAB:
        rec[f'skill_{s}'] = 1 if (s == sk_raw or (len(sk_raw) >= 4 and s in sk_raw)) else 0
    for b in BEHAVIORS_VOCAB:
        is_active = 0
        if b in bh_raw: is_active = 1
        elif b == 'detail oriented' and ('focused' in bh_raw or 'detail' in bh_raw): is_active = 1
        elif b == 'creative' and 'innovative' in bh_raw: is_active = 1
        elif b == 'calm' and 'pressure' in bh_raw: is_active = 1
        elif b == 'extrovert' and 'social' in bh_raw: is_active = 1
        elif b == 'leader' and 'confident' in bh_raw: is_active = 1
        rec[f'behavior_{b}'] = is_active
    real_records.append(rec)

df_real = pd.DataFrame(real_records)

# Domain Competencies
CAREER_COMPETENCIES = {
    'Software': {
        'primary_skills': ['programming', 'problem solving'],
        'secondary_skills': ['teamwork', 'adaptability', 'time management'],
        'primary_behaviors': ['logical'],
        'secondary_behaviors': ['calm', 'introvert', 'detail oriented']
    },
    'AI': {
        'primary_skills': ['programming', 'math', 'problem solving'],
        'secondary_skills': ['critical thinking', 'analytical skills', 'adaptability'],
        'primary_behaviors': ['logical', 'highly motivated'],
        'secondary_behaviors': ['detail oriented', 'introvert']
    },
    'Data Science': {
        'primary_skills': ['analytical skills', 'math', 'critical thinking'],
        'secondary_skills': ['programming', 'time management', 'communication'],
        'primary_behaviors': ['detail oriented', 'logical'],
        'secondary_behaviors': ['calm', 'introvert']
    },
    'Cyber Security': {
        'primary_skills': ['critical thinking', 'problem solving', 'adaptability'],
        'secondary_skills': ['analytical skills', 'programming', 'leadership'],
        'primary_behaviors': ['risk taker', 'detail oriented'],
        'secondary_behaviors': ['calm', 'logical']
    },
    'Medical': {
        'primary_skills': ['critical thinking', 'problem solving', 'communication'],
        'secondary_skills': ['analytical skills', 'adaptability', 'creativity'],
        'primary_behaviors': ['empathetic', 'calm'],
        'secondary_behaviors': ['detail oriented', 'highly motivated', 'introvert']
    },
    'Engineering': {
        'primary_skills': ['problem solving', 'math', 'adaptability'],
        'secondary_skills': ['analytical skills', 'teamwork', 'creativity'],
        'primary_behaviors': ['detail oriented', 'logical'],
        'secondary_behaviors': ['calm', 'leader']
    },
    'Business': {
        'primary_skills': ['leadership', 'communication', 'teamwork'],
        'secondary_skills': ['time management', 'problem solving', 'adaptability'],
        'primary_behaviors': ['leader', 'extrovert'],
        'secondary_behaviors': ['risk taker', 'highly motivated']
    },
    'Arts': {
        'primary_skills': ['creativity', 'communication', 'adaptability'],
        'secondary_skills': ['time management', 'teamwork'],
        'primary_behaviors': ['creative'],
        'secondary_behaviors': ['empathetic', 'introvert', 'extrovert']
    }
}

np.random.seed(42)
aug_records = []
for career, prof in CAREER_COMPETENCIES.items():
    for _ in range(120):
        gpa = round(float(np.clip(np.random.normal(3.15, 0.45), 1.0, 4.0)), 2)
        rec = {'Grade': gpa, 'Target': career}
        skill_vals = {}
        for s in SKILLS_VOCAB:
            p = 0.78 if s in prof['primary_skills'] else (0.35 if s in prof['secondary_skills'] else 0.08)
            skill_vals[s] = 1 if np.random.rand() < p else 0
        if skill_vals.get('programming') == 1 and np.random.rand() < 0.40:
            skill_vals['problem solving'] = 1
        if skill_vals.get('math') == 1 and np.random.rand() < 0.40:
            skill_vals['analytical skills'] = 1
        for s, val in skill_vals.items(): rec[f'skill_{s}'] = val
        behavior_vals = {}
        for b in BEHAVIORS_VOCAB:
            p = 0.78 if b in prof['primary_behaviors'] else (0.35 if b in prof['secondary_behaviors'] else 0.08)
            behavior_vals[b] = 1 if np.random.rand() < p else 0
        for b, val in behavior_vals.items(): rec[f'behavior_{b}'] = val
        aug_records.append(rec)

for career, prof in CAREER_COMPETENCIES.items():
    for s in prof['primary_skills']:
        for b in prof['primary_behaviors']:
            for _ in range(4):
                gpa = round(float(np.clip(np.random.normal(3.15, 0.45), 1.0, 4.0)), 2)
                rec = {'Grade': gpa, 'Target': career}
                for sk in SKILLS_VOCAB: rec[f'skill_{sk}'] = 1 if sk == s else 0
                for bh in BEHAVIORS_VOCAB: rec[f'behavior_{bh}'] = 1 if bh == b else 0
                aug_records.append(rec)

df_aug = pd.DataFrame(aug_records)
df_combined = pd.concat([df_real, df_aug], ignore_index=True)

X = df_combined[feature_cols]
y = le.transform(df_combined['Target'])

X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

# 3. Predict on Test Set
y_pred_test = rf_model.predict(X_test)
cm_test = confusion_matrix(y_test, y_pred_test)
report_test = classification_report(y_test, y_pred_test, target_names=classes, output_dict=True)

# 4. Predict on Real Student Survey Set
X_real = df_real[feature_cols]
y_real = le.transform(df_real['Target'])
y_pred_real = rf_model.predict(X_real)
cm_real = confusion_matrix(y_real, y_pred_real)
report_real = classification_report(y_real, y_pred_real, target_names=classes, output_dict=True)

# Print results in structured JSON format
output_payload = {
    "classes": classes,
    "test_set": {
        "total_samples": len(y_test),
        "accuracy": round(accuracy_score(y_test, y_pred_test) * 100, 2),
        "confusion_matrix": cm_test.tolist(),
        "report": report_test
    },
    "real_dataset": {
        "total_samples": len(y_real),
        "accuracy": round(accuracy_score(y_real, y_pred_real) * 100, 2),
        "confusion_matrix": cm_real.tolist(),
        "report": report_real
    }
}

print(json.dumps(output_payload, indent=2))
