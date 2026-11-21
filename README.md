# CareerPath AI - Smart Counseling For Your Future 🚀

CareerPath AI is an intelligent full-stack career counseling platform that analyzes student skills, interests, and academic backgrounds to predict ideal career pathways using a fine-tuned Machine Learning model.

---

## 🏗️ Project Architecture (Monorepo)

```
careerpath-ai/
├── frontend/        # React 19 + Vite + Tailwind CSS SPA
├── backend/         # Laravel 11 + PHP 8.4 RESTful API + MySQL
└── ml-service/      # Python 3 + FastAPI + Scikit-Learn ExtraTrees ML Model
```

---

## ⚡ Quick Start

### 1. ML Service (FastAPI)
```bash
cd ml-service
pip install -r requirements.txt
uvicorn app:app --host 127.0.0.1 --port 8010
```

### 2. Backend (Laravel API)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --port 8000
```

### 3. Frontend (React Vite)
```bash
cd frontend
npm install
npm run dev
```

---

## 🚀 Cloud Deployment
- **Frontend:** Deploy on Vercel or Railway (Root Directory: `frontend`).
- **Backend:** Deploy on Railway or Render (Root Directory: `backend`).
- **ML Service:** Deploy on Render or Railway (Root Directory: `ml-service`).
- **Database:** Managed MySQL on Railway or Aiven.

---

## 👩‍💻 Author
- **Hamna Muzaffar** ([@hamna019](https://github.com/hamna019))
