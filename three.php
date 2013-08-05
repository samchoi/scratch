<? include 'helper.php'; 
$song = song();
?>

<html>

<head>
<title>sam-choi</title>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">

<script type="text/javascript" src="js/glMatrix-0.9.5.min.js"></script>
<script type="text/javascript" src="js/webgl-utils.js"></script>

<script id="shader-fs" type="x-shader/x-fragment">
    precision mediump float;

    varying vec2 vTextureCoord;

    uniform sampler2D uSampler;

    uniform vec3 uColor;

    void main(void) {
        vec4 textureColor = texture2D(uSampler, vec2(vTextureCoord.s, vTextureCoord.t));
        gl_FragColor = textureColor * vec4(uColor, 1.0);
    }
</script>

<script id="shader-vs" type="x-shader/x-vertex">
    attribute vec3 aVertexPosition;
    attribute vec2 aTextureCoord;

    uniform mat4 uMVMatrix;
    uniform mat4 uPMatrix;

    varying vec2 vTextureCoord;

    void main(void) {
        gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition, 1.0);
        vTextureCoord = aTextureCoord;
    }
</script>

<script type="text/javascript">
var analyser, freqByteData;
    
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

</script>


<script type="text/javascript">

    var gl;

    function initGL(canvas) {
        canvas.width = document.body.clientWidth;
        //canvas.height = document.body.clientHeight;
        try {
            gl = canvas.getContext("experimental-webgl");
            gl.viewportWidth = canvas.width;
            gl.viewportHeight = canvas.height;
        } catch (e) {
        }
        if (!gl) {
            alert("Could not initialise WebGL, sorry :-(");
        }
    }


    function getShader(gl, id) {
        var shaderScript = document.getElementById(id);
        if (!shaderScript) {
            return null;
        }

        var str = "";
        var k = shaderScript.firstChild;
        while (k) {
            if (k.nodeType == 3) {
                str += k.textContent;
            }
            k = k.nextSibling;
        }

        var shader;
        if (shaderScript.type == "x-shader/x-fragment") {
            shader = gl.createShader(gl.FRAGMENT_SHADER);
        } else if (shaderScript.type == "x-shader/x-vertex") {
            shader = gl.createShader(gl.VERTEX_SHADER);
        } else {
            return null;
        }

        gl.shaderSource(shader, str);
        gl.compileShader(shader);

        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
            alert(gl.getShaderInfoLog(shader));
            return null;
        }

        return shader;
    }


    var shaderProgram;

    function initShaders() {
        var fragmentShader = getShader(gl, "shader-fs");
        var vertexShader = getShader(gl, "shader-vs");

        shaderProgram = gl.createProgram();
        gl.attachShader(shaderProgram, vertexShader);
        gl.attachShader(shaderProgram, fragmentShader);
        gl.linkProgram(shaderProgram);

        if (!gl.getProgramParameter(shaderProgram, gl.LINK_STATUS)) {
            alert("Could not initialise shaders");
        }

        gl.useProgram(shaderProgram);

        shaderProgram.vertexPositionAttribute = gl.getAttribLocation(shaderProgram, "aVertexPosition");
        gl.enableVertexAttribArray(shaderProgram.vertexPositionAttribute);

        shaderProgram.textureCoordAttribute = gl.getAttribLocation(shaderProgram, "aTextureCoord");
        gl.enableVertexAttribArray(shaderProgram.textureCoordAttribute);

        shaderProgram.pMatrixUniform = gl.getUniformLocation(shaderProgram, "uPMatrix");
        shaderProgram.mvMatrixUniform = gl.getUniformLocation(shaderProgram, "uMVMatrix");
        shaderProgram.samplerUniform = gl.getUniformLocation(shaderProgram, "uSampler");
        shaderProgram.colorUniform = gl.getUniformLocation(shaderProgram, "uColor");
    }


    function handleLoadedTexture(texture) {
        gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
        gl.bindTexture(gl.TEXTURE_2D, texture);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, texture.image);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);

        gl.bindTexture(gl.TEXTURE_2D, null);
    }


    var starTexture, wordTexture;

    function initTexture() {
        //star
        starTexture = gl.createTexture();
        starTexture.image = new Image();
        starTexture.image.onload = function () {
            handleLoadedTexture(starTexture)
        }

        starTexture.image.src = "star.gif";
        
        //text
        wordTexture = gl.createTexture();
        canvas = document.getElementById('textureCanvas')
        gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
        gl.bindTexture(gl.TEXTURE_2D, wordTexture);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, canvas); // This is the important line!
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR_MIPMAP_NEAREST);
        gl.generateMipmap(gl.TEXTURE_2D);
        gl.bindTexture(gl.TEXTURE_2D, null);
    }


    var mvMatrix = mat4.create();
    var mvMatrixStack = [];
    var pMatrix = mat4.create();

    function mvPushMatrix() {
        var copy = mat4.create();
        mat4.set(mvMatrix, copy);
        mvMatrixStack.push(copy);
    }

    function mvPopMatrix() {
        if (mvMatrixStack.length == 0) {
            throw "Invalid popMatrix!";
        }
        mvMatrix = mvMatrixStack.pop();
    }


    function setMatrixUniforms() {
        gl.uniformMatrix4fv(shaderProgram.pMatrixUniform, false, pMatrix);
        gl.uniformMatrix4fv(shaderProgram.mvMatrixUniform, false, mvMatrix);
    }


    function degToRad(degrees) {
        return degrees * Math.PI / 180;
    }


    var currentlyPressedKeys = {};

    function handleKeyDown(event) {
        currentlyPressedKeys[event.keyCode] = true;
    }


    function handleKeyUp(event) {
        currentlyPressedKeys[event.keyCode] = false;
    }


    var zoom = 0;


    var tilt = 0;
    var spin = 0;
    var rotate = 0;


    function handleKeys() {
        if (currentlyPressedKeys[33]) {
            // Page Up
            //zoom -= 0.1;
        }
        if (currentlyPressedKeys[34]) {
            // Page Down
            //zoom += 0.1;
        }
        if (currentlyPressedKeys[38]) {
            // Up cursor key
            //tilt += 2;
            document.getElementById('flicker').value = parseFloat(document.getElementById('flicker').value) + .01
        }
        if (currentlyPressedKeys[40]) {
            // Down cursor key
            //tilt -= 2;
             document.getElementById('flicker').value = parseFloat(document.getElementById('flicker').value) - .01
        }
        if (currentlyPressedKeys[37]) {
            // left cursor key
            //rotate -= 2;
        }
        if (currentlyPressedKeys[39]) {
            // right cursor key
            //rotate += 2;
        }

    }


    var starVertexPositionBuffer;
    var starVertexTextureCoordBuffer;

    function initBuffers() {
        starVertexPositionBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, starVertexPositionBuffer);
        vertices = [
            -1.0, -1.0,  0.0,
             1.0, -1.0,  0.0,
            -1.0,  1.0,  0.0,
             1.0,  1.0,  0.0
        ];
        
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertices), gl.STATIC_DRAW);
        starVertexPositionBuffer.itemSize = 3;
        starVertexPositionBuffer.numItems = 4;

        starVertexTextureCoordBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, starVertexTextureCoordBuffer);
        var textureCoords = [
            0.0, 0.0,
            1.0, 0.0,
            0.0, 1.0,
            1.0, 1.0
        ];
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(textureCoords), gl.STATIC_DRAW);
        starVertexTextureCoordBuffer.itemSize = 2;
        starVertexTextureCoordBuffer.numItems = 4;
    }


    function drawStar() {
        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, starTexture);
        gl.uniform1i(shaderProgram.samplerUniform, 0);

        gl.bindBuffer(gl.ARRAY_BUFFER, starVertexTextureCoordBuffer);
        gl.vertexAttribPointer(shaderProgram.textureCoordAttribute, starVertexTextureCoordBuffer.itemSize, gl.FLOAT, false, 0, 0);

        gl.bindBuffer(gl.ARRAY_BUFFER, starVertexPositionBuffer);
        gl.vertexAttribPointer(shaderProgram.vertexPositionAttribute, starVertexPositionBuffer.itemSize, gl.FLOAT, false, 0, 0);

        setMatrixUniforms();
        gl.drawArrays(gl.TRIANGLE_STRIP, 0, starVertexPositionBuffer.numItems);
    }
    
    var max_height = 2.0;

    function Star(startingDistance, rotationSpeed, start_height) {
        this.angle = 0;
        this.dist = startingDistance;
        this.height = 0;
        this.last_height = 0;
        this.zoom = Math.random()*250;
        this.rotationSpeed = rotationSpeed;
        this.start_height = start_height; 
        this.flyout = false;
        // Set the colors to a starting value.
        this.randomiseColors();
    }
    Star.prototype.draw = function (tilt, rotate, spin) {
        mvPushMatrix();

        // Move to the star's position
        mat4.rotate(mvMatrix, degToRad(this.angle), [0.0, 1.0, 0.0]);

        mat4.translate(mvMatrix, [this.dist-25, this.start_height+this.height*2, -this.zoom]);

        // Rotate back so that the star is facing the viewer
        mat4.rotate(mvMatrix, degToRad(-this.angle), [0.0, 1.0, 0.0]);
        mat4.rotate(mvMatrix, degToRad(-tilt), [0.0, 1.0, 0.0]);
        
        if (this.height > this.last_height && this.height > document.getElementById("flicker").value) {
            // Draw a non-rotating star in the alternate "twinkling" color
            gl.uniform3f(shaderProgram.colorUniform, this.twinkleR, this.twinkleG, this.twinkleB);
            drawStar();
        }

        // All stars spin around the Z axis at the same rate
        mat4.rotate(mvMatrix, degToRad(spin), [0.0, 0.0, 1.0]);

        // Draw the star in its main color
        gl.uniform3f(shaderProgram.colorUniform, this.r, this.g, this.b);
        if(this.height > document.getElementById("flicker").value){
            drawStar();
        }

        mvPopMatrix();
    };


    var effectiveFPMS = 60 / 1000;
    Star.prototype.animate = function (elapsedTime, height) {
        this.angle += this.rotationSpeed * effectiveFPMS * elapsedTime;

        // Decrease the distance, resetting the star to the outside of
        // the spiral if it's at the center.
        if (this.dist < 0.0) {
            //this.dist += 5.0;
            //this.randomiseColors();
        }
        
        
        
        if (this.flyout){
            this.zoom -= .25;
        }else if(Math.random()*10 > 8){
            this.flyout = true;
        }
        
        if (this.zoom < -25){
            this.zoom = 100;
            this.dist = Math.random()*50;
            this.flyout = false;
            if(stars.length <= 512){
                stars.push(new Star((stars.length/512) * 50.0, 0, Math.random()));                
            }
        }

        this.last_height = this.height;
        this.height = height/1000;
        
    };


    Star.prototype.randomiseColors = function () {
        // Give the star a random color for normal
        // circumstances...
        this.r = Math.random();
        this.g = Math.random();
        this.b = Math.random();

        // When the star is twinkling, we draw it twice, once
        // in the color below (not spinning) and then once in the
        // main color defined above.
        this.twinkleR = Math.random();
        this.twinkleG = Math.random();
        this.twinkleB = Math.random();
    };



    var stars = [];

    function initWorldObjects() {
        var numStars = 50;

        for (var i=0; i < numStars; i++) {
            //stars.push(new Star((i / numStars) * 5.0, i / numStars));
            stars.push(new Star((i / numStars) * 50.0, 0, Math.random()));
        }
    }


    function drawScene() {
        gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

        mat4.perspective(45, gl.viewportWidth / gl.viewportHeight, 0.1, 100.0, pMatrix);

        gl.blendFunc(gl.SRC_ALPHA, gl.ONE);
        gl.enable(gl.BLEND);

        mat4.identity(mvMatrix);
        mat4.translate(mvMatrix, [0.0, 0.0, zoom]);
        mat4.rotate(mvMatrix, degToRad(tilt), [1.0, 0.0, 0.0]);
        mat4.rotate(mvMatrix, degToRad(rotate), [1.0, 1.0, 0.0]);

        for (var i in stars) {
            stars[i].draw(tilt, rotate, spin);
            //spin += 0.1;
        }

    }


    var lastTime = 0;

    function animate() {
        var timeNow = new Date().getTime();
        freqByteData = new Uint8Array(analyser.frequencyBinCount);
        analyser.getByteFrequencyData(freqByteData);
        
        
        
        if (lastTime != 0) {
            var elapsed = timeNow - lastTime;

            for (var i in stars) {
                stars[i].animate(elapsed, freqByteData[i*2]*10);
            }
        }
        lastTime = timeNow;

    }

    function specDataDebugString(unitArr){
        var myString = '';
        for (var i=0; i<unitArr.byteLength; i++) {
            myString += '[' + String.fromCharCode(unitArr[i]) + ']';
        }
        return myString;
    }

    function tick() {
        requestAnimFrame(tick);
        handleKeys();
        drawScene();
        animate();
    }



    function webGLStart() {
        var canvas = document.getElementById("lesson09-canvas");
        initGL(canvas);
        initShaders();
        initBuffers();
        initTexture();
        initWorldObjects();

        setupWebAudio();
        gl.clearColor(0.0, 0.0, 0.0, 1.0);

        document.onkeydown = handleKeyDown;
        document.onkeyup = handleKeyUp;
        canvas.focus();
        tick();
    }

</script>


</head>


<body onload="webGLStart();" style="background-color: black;">
    <a style="display:none" href="http://learningwebgl.com/">&lt;&lt; Back to Web GL</a>
    <div id="debug"></div>
    <audio id="music" src="/music/<?= $song ?>" preload="auto"></audio>
    <input style="display:none" type="text" id="flicker" value="0" />
    <canvas style="display:none" id="textureCanvas">I'm sorry your browser does not support the HTML5 canvas element.</canvas>
    <script>
	var canvas =  document.getElementById('textureCanvas');
	canvas.height = 25;
	//canvas.width = 50;
        canvas.style.backgroundColor = 'green';
        var ctx = canvas.getContext('2d');
	
        ctx.fillStyle = "#333333"; 	// This determines the text colour, it can take a hex value or rgba value (e.g. rgba(255,0,0,0.5))
        ctx.textAlign = "center";	// This determines the alignment of text, e.g. left, center, right
        ctx.textBaseline = "middle";	// This determines the baseline of the text, e.g. top, middle, bottom
        ctx.font = "12px monospace";	// This determines the size of the text and the font family used
        ctx.fillText("<?= $song ?>", canvas.width/2, canvas.height/2);
    </script>
    <canvas id="lesson09-canvas" style="border: none;" width="1024" height="500"></canvas>
</body>

</html>