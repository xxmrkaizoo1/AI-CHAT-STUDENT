import sys
import os
import joblib

# get absolute path of this file
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# correct path to model
MODEL_PATH = os.path.join(BASE_DIR, "model.joblib")

text = sys.argv[1] if len(sys.argv) > 1 else ""

model = joblib.load(MODEL_PATH)
prediction = model.predict([text])[0]

print(prediction)
