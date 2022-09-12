const audioContext = new AudioContext();
const htmlAudioElement = document.getElementById("audio");
const source = audioContext.createMediaElementSource(htmlAudioElement);
source.connect(audioContext.destination);

const levelRangeElement = document.getElementById("levelRange");

if (typeof Meyda === "undefined") {
  console.log("Meyda could not be found! Have you included it?");
}
else {
  const analyzer = Meyda.createMeydaAnalyzer({
    "audioContext": audioContext,
    "source": source,
    "bufferSize": 512,
    "featureExtractors": ["rms"],
    "callback": features => {
      console.log(features);
      levelRangeElement.value = features.rms;
    }
  });
  analyzer.start();
}

async function load() {
  const model = await tf.loadLayersModel('model_js/model.json');
  return model;
};

function predict(model) {
  // code to connect to the <input> given value will go here (just not yet)
  const inputTensor = tf.tensor([parseInt(userInput)]);  // then convert to tensor

  // now lets make the prediction, we use .then because the model is a promise
  // (this is confusing as a Python user, but useful so check it out if interested)
  model.then(model => {
    let result = model.predict(inputTensor);  // make prediction like in Python
    result = result.round().dataSync()[0];  // round prediction and get value
    alert(result ? "odd" : "even");  // creates pop-up, if result == 1 shows 'odd', otherwise 'even'
  });
};

const model = load();  // load the model now to prevent any delay when user clicks 'Predict'