import os
import pandas as pd
import joblib
from sklearn.pipeline import Pipeline
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(BASE_DIR, "data.csv")
MODEL_PATH = os.path.join(BASE_DIR, "model.joblib")

df = pd.read_csv(DATA_PATH)

# clean
df = df.dropna()
df["text"] = df["text"].astype(str)
df["label"] = df["label"].astype(str)

X = df["text"]
y = df["label"]

model = Pipeline([
    ("tfidf", TfidfVectorizer()),
    ("clf", LogisticRegression(max_iter=1000))
])

model.fit(X, y)

joblib.dump(model, MODEL_PATH)
print("✅ Model saved:", MODEL_PATH)
print("✅ Rows trained:", len(df))
