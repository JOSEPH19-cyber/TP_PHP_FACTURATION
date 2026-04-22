/**
 * Scanner de codes-barres avec ZXing
 */
let codeReaderZXing = null;
let isScanningZXing = false;
let videoElement = null;

function demarrerScannerZXing(callback) 
{

    isScanningZXing = true;
    codeReaderZXing = new ZXing.BrowserMultiFormatReader();
    
    const viewport = document.getElementById('scanner-viewport');
    
    viewport.innerHTML = '';
    videoElement = document.createElement('video');
    videoElement.setAttribute('id', 'scanner-video-element');
    videoElement.setAttribute('autoplay', 'true');
    videoElement.setAttribute('playsinline', 'true');
    videoElement.style.width = '100%';
    videoElement.style.height = 'auto';
    viewport.appendChild(videoElement);
    
    codeReaderZXing.decodeFromVideoDevice(
        null,
        videoElement,
        (result, err) => 
        {

            if (result && isScanningZXing) 
            {

                const code = result.text;
                console.log("Code détecté:", code);
                arreterScannerZXing();

                if (callback) callback(code, null);

            }
            if (err && !(err instanceof ZXing.NotFoundException) && isScanningZXing) 
            {

                console.error("Erreur:", err);
                arreterScannerZXing();

                if (callback) callback(null, "Erreur de lecture");

            }

        }

    );

}

function arreterScannerZXing() 
{

    isScanningZXing = false;
    if (codeReaderZXing) 
    {

        codeReaderZXing.reset();
        codeReaderZXing = null;

    }

    if (videoElement) 
    {

        videoElement.pause();
        videoElement.srcObject = null;
        videoElement = null;

    }

    const viewport = document.getElementById('scanner-viewport');

    if (viewport) 
    {

        viewport.innerHTML = '';

    }

}

const Scanner = 
{
    
    demarrer: function(callback)
    {

        demarrerScannerZXing(callback);

    },
    arreter: function() 
    {

        arreterScannerZXing();

    }

};