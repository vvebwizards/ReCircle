import pandas as pd
import numpy as np
import re
import string
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.model_selection import train_test_split, GridSearchCV
from sklearn.pipeline import Pipeline
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import classification_report, accuracy_score
import joblib
import torch
import torch.nn as nn
import torch.optim as optim
from torch.utils.data import Dataset, DataLoader

# Sample data for severity classification
data = pd.DataFrame([
    # High Severity - Critical issues affecting core functionality, security, or urgent matters
    ("My payment failed and money was deducted", "high"),
    ("Account hacked unauthorized access", "high"),
    ("All my data has been deleted", "high"),
    ("Security breach personal information exposed", "high"),
    ("Payment gateway compromised", "high"),
    ("Cannot access my account at all", "high"),
    ("Financial fraud detected", "high"),
    ("Server down completely", "high"),
    ("Data loss critical information gone", "high"),
    ("Privacy violation serious issue", "high"),
    ("Payment system not working at all", "high"),
    ("Complete system outage", "high"),
    ("Emergency account lock", "high"),
    ("Critical bug affecting all users", "high"),
    ("Urgent security vulnerability", "high"),

    # Medium Severity - Important issues affecting functionality but not critical
    ("App crashes occasionally when opening", "medium"),
    ("Some features not working properly", "medium"),
    ("Delivery delayed by 2 days", "medium"),
    ("Payment failed but no money deducted", "medium"),
    ("Cannot upload large files", "medium"),
    ("Slow performance in the app", "medium"),
    ("Some buttons not responding", "medium"),
    ("Minor display issues", "medium"),
    ("Notification not working consistently", "medium"),
    ("Login issues sometimes", "medium"),
    ("Profile picture not updating", "medium"),
    ("Search function not accurate", "medium"),
    ("Order status not updating", "medium"),
    ("App freezes occasionally", "medium"),
    ("Some pages load slowly", "medium"),

    # Low Severity - Minor issues, suggestions, cosmetic problems
    ("Suggestion for improvement", "low"),
    ("Color scheme could be better", "low"),
    ("Minor typo in the interface", "low"),
    ("Small UI alignment issue", "low"),
    ("Feature request for future", "low"),
    ("General feedback about service", "low"),
    ("Question about how to use feature", "low"),
    ("Small text formatting issue", "low"),
    ("Icon looks pixelated", "low"),
    ("Font size too small", "low"),
    ("Button color could be improved", "low"),
    ("Suggestion for new feature", "low"),
    ("Minor spelling mistake", "low"),
    ("Layout could be more intuitive", "low"),
    ("Small improvement suggestion", "low"),

    # Additional edge cases
    ("URGENT!!! PAYMENT FAILED MONEY TAKEN", "high"),
    ("emergency account access lost", "high"),
    ("critical system error", "high"),
    ("app not working properly", "medium"),
    ("delivery late again", "medium"),
    ("small suggestion for app", "low"),
    ("minor issue with design", "low"),
    ("HELP! CANNOT ACCESS ACCOUNT", "high"),
    ("security issue found", "high"),
    ("just a small idea", "low")
], columns=["description", "severity"])

# Preprocessing function
def preprocess_text(text):
    text = text.lower()
    text = text.translate(str.maketrans('', '', string.punctuation))
    text = re.sub(r'\d+', '', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text

data['description'] = data['description'].apply(preprocess_text)

# Split dataset
X = data['description']
y = data['severity']
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

# Sklearn Pipeline
pipeline = Pipeline([
    ('tfidf', TfidfVectorizer(
        stop_words='english',
        ngram_range=(1, 2),
        max_df=0.95,
        min_df=1,
        max_features=5000
    )),
    ('clf', LogisticRegression(
        solver='saga',
        multi_class='multinomial',
        max_iter=1000,
        random_state=42
    ))
])

# Grid search
param_grid = {
    'tfidf__max_df': [0.85, 0.95],
    'tfidf__ngram_range': [(1, 1), (1, 2)],
    'clf__C': [0.1, 1, 10]
}

grid_search = GridSearchCV(pipeline, param_grid, cv=5, scoring='accuracy', n_jobs=-1)
grid_search.fit(X_train, y_train)

# Best model
best_model = grid_search.best_estimator_

# Evaluation
y_pred = best_model.predict(X_test)
print("Sklearn Model Evaluation:")
print(classification_report(y_test, y_pred))
print(f"Accuracy: {accuracy_score(y_test, y_pred):.2f}")

# Save sklearn model
joblib.dump(best_model, "sklearn_model.joblib")
joblib.dump(best_model.named_steps['tfidf'], "sklearn_vectorizer.joblib")

print("Model training completed and saved!")