const currentVersion = '1.1.0'; // Update this with your current version
fetch('https://aliev.io/home/_api/UI/terminal/Tools/qr/SmartyQR/version.txt')
    .then(response => response.text())
    .then(newVersion => {
        if (currentVersion !== newVersion) {
            alert(`Update available! Current version: ${currentVersion}. New version: ${newVersion}. Please download the latest version.`);
        }
    });


$(document).ready(function() {
    // Check for the productInfo div
    const featureBoxRight = $('div.productInfo');

    if (featureBoxRight.length > 0) {
        // Select the first element with class "productInfo-item" within the "productInfo" div
        const productInfoItem = featureBoxRight.find('.productInfo-item').first();
        
        if (productInfoItem.length > 0) {
            // Create a button element
            const qrButton = $('<button>Generate QR Code</button>').css('margin-left', '10px');

            // Create a div to hold the QR code
            const qrCodeContainer = $('<div></div>').css({
                display: 'none',
                position: 'fixed',
                top: '10px',
                left: '50%',
                transform: 'translateX(-50%)',
                border: '1px solid #000',
                padding: '10px',
                backgroundColor: '#fff',
                zIndex: '1000'
            });

            // Insert the button next to the productInfo-item
            productInfoItem.append(qrButton);
            productInfoItem.append(qrCodeContainer);

            // Function to generate QR code
            function generateQRCode(text) {
                qrCodeContainer.empty();
                new QRCode(qrCodeContainer[0], {
                    text: text,
                    width: 128,
                    height: 128
                });
            }

            // Event listener for click
            qrButton.on('click', function() {
                const textToEncode = productInfoItem.find('.selectableText').text();
                generateQRCode(textToEncode);
                qrCodeContainer.toggle(); // Toggle visibility on click
            });
        }
    }
});
