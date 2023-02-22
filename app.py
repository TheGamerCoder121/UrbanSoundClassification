import librosa
import numpy as np
import tensorflow as tf
from tensorflow import keras
from flask import Flask, request, jsonify, render_template

app = Flask(__name__)

# Load the model
model = keras.models.load_model('model.json')
labelencoder = ['air_conditioner', 'car_horn', 'children_playing', 'dog_bark', 'drilling', 'engine_idling', 'gun_shot', 'jackhammer', 'siren', 'street_music']

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/predict', methods=['POST'])
def predict():
    # Get the file from the request
    file = request.files['file']
    # Load the audio file
    audio, sample_rate = librosa.load(file, res_type='kaiser_fast') 
    # Extract features from the audio
    mfccs_features = librosa.feature.mfcc(y=audio, sr=sample_rate, n_mfcc=40)
    mfccs_scaled_features = np.mean(mfccs_features.T,axis=0)
    mfccs_scaled_features = mfccs_scaled_features.reshape(1,-1)
    # Make a prediction
    predicted_label = model.predict(mfccs_scaled_features)
    # Convert the predicted label to a class name
    classes_x = np.argmax(predicted_label,axis=1)
    prediction_class = labelencoder[classes_x[0]]

    # Return the prediction as a JSON object
    return jsonify({'prediction': prediction_class})

if __name__ == '__main__':
    app.run(debug=True)
