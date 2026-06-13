document.addEventListener('DOMContentLoaded', function() {
    // Inject styles for custom loader
    const style = document.createElement('style');
    style.innerHTML = `
        .custom-loader {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: transparent;
        }

        .loader-logo-container {
            perspective: 1000px;
            margin-bottom: 25px;
        }

        .loader-rotating-logo {
            width: 100px;
            height: auto;
            animation: loader-rotateY 2s infinite linear;
            transform-style: preserve-3d;
        }

        @keyframes loader-rotateY {
            0% { transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); }
        }

        .loader-text {
            color: #c29545; /* Var primary */
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
            letter-spacing: 1px;
        }

        .loader-bar-container {
            width: 100%;
            max-width: 250px;
            height: 6px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .loader-bar {
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, #c29545, transparent);
            border-radius: 10px;
            position: absolute;
            left: -50%;
            animation: loader-loading 1.5s infinite ease-in-out;
        }

        @keyframes loader-loading {
            0% { left: -50%; }
            100% { left: 100%; }
        }

        .custom-swal-loader-popup {
            background: #161616 !important;
            border: 1px solid rgba(194, 149, 69, 0.3) !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8) !important;
            padding: 10px !important;
        }

        .custom-swal-loader-popup .swal2-html-container {
            margin: 0;
            padding: 0;
        }
    `;
    document.head.appendChild(style);
});

window.mostrarCarga = function(texto) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            html: `
                <div class="custom-loader">
                    <div class="loader-logo-container">
                        <img src="/img/LogoRestaurant.png" alt="Logo" class="loader-rotating-logo">
                    </div>
                    <div class="loader-text">${texto}</div>
                    <div class="loader-bar-container">
                        <div class="loader-bar"></div>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            customClass: {
                popup: 'custom-swal-loader-popup'
            },
            background: '#161616'
        });
    }
};
