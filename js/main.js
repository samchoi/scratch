/*
 * Created by Cameron Adams on 18th September 2012
 * More info here: http://themaninblue.com/writing/perspective/2012/09/18/
 *
 */

var analyser, canvas, canvasContext;

window.addEventListener('load', init, false);

function init() {
	setupWebAudio();
	setupDrawingCanvas();
	draw();
}


// Wire up the <audio> element with the Web Audio analyser (currently Webkit only)
function setupWebAudio() {
	// Get our <audio> element
	var audio = document.getElementById('music');
	// Create a new audio context (that allows us to do all the Web Audio stuff)
	var audioContext = new webkitAudioContext();
	// Create a new analyser
	analyser = audioContext.createAnalyser();
	// Create a new audio source from the <audio> element
	var source = audioContext.createMediaElementSource(audio);
	// Connect up the output from the audio source to the input of the analyser
	source.connect(analyser);
	// Connect up the audio output of the analyser to the audioContext destination i.e. the speakers (The analyser takes the output of the <audio> element and swallows it. If we want to hear the sound of the <audio> element then we need to re-route the analyser's output to the speakers)
	analyser.connect(audioContext.destination);

	// Get the <audio> element started	
	audio.play();
}

var color = [0, 0, 0], inc = 20, dir = [1, -1, 1];

// Draw the audio frequencies to screen
function draw() {
	// Setup the next frame of the drawing
  webkitRequestAnimationFrame(draw);
  
  // Create a new array that we can copy the frequency data into
	var freqByteData = new Uint8Array(analyser.frequencyBinCount);
	// Copy the frequency data into our new array
	analyser.getByteFrequencyData(freqByteData);

	// Clear the drawing display
	canvasContext.clearRect(0, 0, canvas.width, canvas.height);
    var to = 0;
	// For each "bucket" in the frequency data, draw a line corresponding to its magnitude
    var row=-1; 
    for (var i = 0; i <= freqByteData.length; i++) {
	var col=0, x_max = 16, y_max=64;
	    col = i%y_max;
	    if (col == 1){
		row += 1; 
	    }
	    var id = row+'_'+col;
	var diff = 255, v=freqByteData[i];
	var pct = v/255;
	var box = document.getElementById(id);
	var diff= $('#color').spectrum("get").toRgb();
	var r =  diff['r']-v,
	g = diff['g']-v,
	b = diff['b']-v;
	if(box !== null){
	    box.style.backgroundColor = 'rgba(' + r +', '+ g + ', ' + b  + ', 1)';
	    box.style.opacity = pct;
	}else{
	    //console.log(id);
	}
//canvasContext.fillRect(i, canvas.height - freqByteData[i], 1, canvas.height);
	if (freqByteData[i]){
	    to += freqByteData[i];
	}    
	}
    if(to > 10000){
	i = Math.floor((Math.random()*3))
	if (color[i] > 255 || color[i] < 0){
	    dir[i] *= -1 
	}
	color[i] += dir[i]*inc; //* Math.floor((Math.random()*5));

	document.getElementById('trip').innerHTML=to;
	setColorByRgb(color[0], color[1], color[2]);
    }
}

function setColor(){
    $('#color').spectrum('set', "rgb("+Math.floor((Math.random()*255))+", "+Math.floor((Math.random()*255))+", "+Math.floor((Math.random()*255))+")");

}

function setColorByRgb(r, g, b){
    console.log("rgb("+r+", "+g+", "+b+")");
    $('#color').spectrum('set', "rgb("+r+", "+g+", "+b+")");
}

// Basic setup for the canvas element, so we can draw something on screen
function setupDrawingCanvas() {
    canvas = document.createElement('canvas');
    // 1024 is the number of samples that's available in the frequency data
    canvas.width = 0;//1024;
    // 255 is the maximum magnitude of a value in the frequency data
    canvas.height = 0;//255;
    document.body.appendChild(canvas);
    canvasContext = canvas.getContext('2d');
    canvasContext.fillStyle = '#ffffff';
}

function sum(Array) {
    return this.reduce(function(a,b){return a+b;});
}




$( document ).ready(function() {
    //jquery doesnt fetch html5
    var music = document.getElementById('music');

    $("#color").spectrum({
	color: "rgb("+Math.floor((Math.random()*255))+", "+Math.floor((Math.random()*255))+", "+Math.floor((Math.random()*255))+")"
    }).change(function(){
	
    });

    //setInterval(setColor, 2000)

$('#file_info').on('click', function(){
    console.log(music.paused);
    music.paused ? music.play() : music.pause();
});




});
