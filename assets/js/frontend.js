/**
 * Frontend JavaScript for Topbar Countdown Notice
 * Enhanced with smooth transitions and better color management
 */

(function () {
    'use strict';

    // Debug logging function - only logs if debug mode is enabled
    function debugLog() {
        if (typeof tcnData !== 'undefined' && tcnData.debugMode) {
            console.log.apply(console, arguments);
        }
    }

    debugLog('═══════════════════════════════════════════════════');
    debugLog('🚀 TCN Frontend JavaScript - Cargado correctamente');
    debugLog('═══════════════════════════════════════════════════');

    // Store original styles for smooth transitions
    var originalStyles = {
        transition: '',
        backgroundColor: '',
        color: ''
    };

    // Adjust body padding to prevent content from being hidden under the fixed bar
    function adjustBodyPadding() {
        var topbar = document.getElementById('tcn-topbar');
        if (!topbar) return;

        var height = topbar.offsetHeight;
        var currentPadding = parseFloat(window.getComputedStyle(document.body).paddingTop) || 0;

        // Only adjust if the new padding is different
        if (Math.abs(height - currentPadding) > 1) {
            document.body.style.paddingTop = height + 'px';
        }
    }

    // Save original styles before any transitions
    function saveOriginalStyles(element) {
        if (!element) return;

        originalStyles = {
            transition: element.style.transition,
            backgroundColor: element.style.backgroundColor,
            color: element.style.color,
            height: element.style.height,
            opacity: element.style.opacity
        };
    }

    // Apply smooth transition to element
    function applyTransition(element, properties, duration = 300) {
        if (!element) return;

        // Save current styles if not already saved
        if (!originalStyles.transition) {
            saveOriginalStyles(element);
        }

        // Apply transition
        element.style.transition = Object.entries(properties)
            .map(([prop]) => `${prop} ${duration}ms ease-in-out`)
            .join(', ');

        // Apply new styles
        Object.entries(properties).forEach(([prop, value]) => {
            if (value !== undefined) {
                element.style[prop] = value;
            }
        });

        // Return a promise that resolves when transition ends
        return new Promise(resolve => {
            const onTransitionEnd = () => {
                element.removeEventListener('transitionend', onTransitionEnd);
                resolve();
            };
            element.addEventListener('transitionend', onTransitionEnd);

            // Fallback in case transitionend doesn't fire
            setTimeout(resolve, duration + 100);
        });
    }

    // Smooth transition to alternative content
    async function transitionToAlternative() {
        debugLog('🎨 transitionToAlternative() - Iniciando...');

        const topbar = document.getElementById('tcn-topbar');
        if (!topbar) {
            debugLog('❌ Error: No se encontró el elemento #tcn-topbar');
            return;
        }

        if (!tcnData) {
            debugLog('❌ Error: tcnData no está definido');
            return;
        }

        const finishAction = tcnData.finishAction;
        debugLog('🎬 Acción a ejecutar:', finishAction);

        const contentDiv = topbar.querySelector('.tcn-content');
        const countdownWrapper = topbar.querySelector('.tcn-countdown-wrapper');
        const linkElement = topbar.querySelector('.tcn-link');

        if (finishAction === 'hide') {
            debugLog('👻 Ocultando la barra...');
            // Smooth collapse and fade out
            await applyTransition(topbar, {
                opacity: '0',
                height: '0',
                padding: '0',
                margin: '0',
                overflow: 'hidden'
            }, 500);

            topbar.style.display = 'none';
            adjustBodyPadding();
            debugLog('✅ Barra ocultada correctamente');

        } else if (finishAction === 'show_alternative' && tcnData.alternativeText) {
            debugLog('🔄 Mostrando contenido alternativo...');
            // First, fade out current content
            await applyTransition(topbar, { opacity: '0' });

            // Update colors if custom colors are enabled
            if (tcnData.alternativeColorsMode === 'custom') {
                debugLog('🎨 Aplicando colores personalizados:', tcnData.alternativeBgColor, tcnData.alternativeTextColor);
                topbar.style.backgroundColor = tcnData.alternativeBgColor || '';
                topbar.style.color = tcnData.alternativeTextColor || '';

                // Update link color if it exists
                if (linkElement) {
                    linkElement.style.color = tcnData.alternativeTextColor || '';
                    linkElement.style.borderColor = tcnData.alternativeTextColor || '';
                }
            }

            // Update content
            if (contentDiv) {
                debugLog('📝 Actualizando contenido:', tcnData.alternativeText.substring(0, 50) + '...');
                contentDiv.innerHTML = tcnData.alternativeText;
            }

            // Hide countdown if it exists
            if (countdownWrapper) {
                debugLog('⏱️  Ocultando countdown');
                countdownWrapper.style.display = 'none';
            }

            // Update or create link
            if (tcnData.alternativeLink) {
                debugLog('🔗 Configurando enlace alternativo:', tcnData.alternativeLink);
                if (!linkElement) {
                    const newLink = document.createElement('a');
                    newLink.className = 'tcn-link';
                    newLink.style.transition = 'all 0.3s ease';
                    topbar.querySelector('.tcn-topbar-inner').appendChild(newLink);
                    newLink.href = tcnData.alternativeLink;
                    newLink.textContent = tcnData.alternativeLinkText || 'Learn more';

                    if (tcnData.alternativeLinkTab) {
                        newLink.target = '_blank';
                        newLink.rel = 'noopener noreferrer';
                    }

                    // Apply alternative text color if custom colors are enabled
                    if (tcnData.alternativeColorsMode === 'custom' && tcnData.alternativeTextColor) {
                        newLink.style.color = tcnData.alternativeTextColor;
                        newLink.style.borderColor = tcnData.alternativeTextColor;
                    }
                } else {
                    linkElement.href = tcnData.alternativeLink;
                    linkElement.textContent = tcnData.alternativeLinkText || 'Learn more';
                    linkElement.style.display = 'inline-block';

                    if (tcnData.alternativeLinkTab) {
                        linkElement.target = '_blank';
                        linkElement.rel = 'noopener noreferrer';
                    } else {
                        linkElement.removeAttribute('target');
                        linkElement.removeAttribute('rel');
                    }
                }
            } else if (linkElement) {
                linkElement.style.display = 'none';
            }

            // Fade in with new content
            debugLog('✨ Mostrando contenido alternativo con fade in');
            await applyTransition(topbar, { opacity: '1' });
            debugLog('✅ Transición a contenido alternativo completada');
        } else {
            debugLog('⚠️  No se ejecutó ninguna acción (finishAction:', finishAction, ', alternativeText:', !!tcnData.alternativeText, ')');
        }
    }

    // Initialize countdown timer
    function initCountdown() {
        var countdownElement = document.getElementById('tcn-countdown');
        if (!countdownElement) {
            debugLog('TCN Countdown: Elemento countdown no encontrado');
            return;
        }

        // Get target timestamp from localized data
        var targetTimestamp = parseInt(tcnData.countdownTarget, 10);
        if (!targetTimestamp || targetTimestamp <= 0) {
            debugLog('TCN Countdown: No hay target timestamp configurado');
            return;
        }

        // Calculate time difference between server and client
        var serverTime = parseInt(tcnData.serverTime, 10);
        var clientTime = Math.floor(Date.now() / 1000);
        var timeDifference = serverTime - clientTime;

        // Log initial configuration
        debugLog('═══════════════════════════════════════════════════');
        debugLog('TCN Countdown - Configuración Inicial');
        debugLog('═══════════════════════════════════════════════════');

        var targetDate = new Date(targetTimestamp * 1000);
        debugLog('🎯 Fecha/Hora de Finalización:', targetDate.toLocaleString());
        debugLog('⏰ Timestamp de Finalización:', targetTimestamp);
        debugLog('🕐 Tiempo del Servidor:', new Date(serverTime * 1000).toLocaleString());
        debugLog('🕐 Tiempo del Cliente:', new Date(clientTime * 1000).toLocaleString());
        debugLog('⚖️  Diferencia (servidor - cliente):', timeDifference, 'segundos');
        debugLog('🎬 Acción a Realizar:', tcnData.finishAction || 'hide');

        if (tcnData.finishAction === 'show_alternative') {
            debugLog('📝 Texto Alternativo:', tcnData.alternativeText ? 'Configurado' : 'No configurado');
            debugLog('🔗 Enlace Alternativo:', tcnData.alternativeLink || 'No configurado');
            debugLog('🎨 Colores Personalizados:', tcnData.alternativeColorsMode === 'custom' ? 'Sí' : 'No');
        }

        // Use server-synchronized time for accurate countdown
        var now = Math.floor(Date.now() / 1000) + timeDifference;
        var diff = targetTimestamp - now;
        debugLog('⏱️  Tiempo Restante (segundos):', diff);
        debugLog('═══════════════════════════════════════════════════');

        var labels = tcnData.labels || {
            days: 'd',
            hours: 'h',
            minutes: 'm',
            seconds: 's'
        };

        // Variable to track if countdown has ended (in this session only)
        var countdownEnded = false;

        // If countdown already ended, show the final state
        if (diff <= 0) {
            debugLog('🏁 Ejecutando acción de finalización inmediatamente');
            handleCountdownEnd();
            return;
        }

        // Function to format time values
        function formatTime(value) {
            return value < 10 ? '0' + value : value;
        }

        // Function to update the countdown display
        function updateDisplay(days, hours, minutes, seconds) {
            var display = [];

            // Always show days, hours, and minutes (even if 0)
            display.push(days + labels.days);
            display.push(formatTime(hours) + labels.hours);
            display.push(formatTime(minutes) + labels.minutes);

            // Show seconds only if enabled in settings
            if (tcnData.showSeconds) {
                display.push(formatTime(seconds) + labels.seconds);
            }

            countdownElement.textContent = display.join(' ');
        }

        // Handle countdown completion
        function handleCountdownEnd() {
            debugLog('═══════════════════════════════════════════════════');
            debugLog('🏁 COUNTDOWN FINALIZADO - Ejecutando acción');
            debugLog('🎬 Acción:', tcnData.finishAction);
            debugLog('═══════════════════════════════════════════════════');

            // Update display to show zeros
            updateDisplay(0, 0, 0, 0);

            // Trigger the transition
            debugLog('🎨 Iniciando transición...');
            transitionToAlternative();
        }

        // Main update function
        function updateCountdown() {
            // Get current time in seconds (synchronized with server)
            var now = Math.floor(Date.now() / 1000) + timeDifference;
            var diff = targetTimestamp - now;

            // If countdown finished
            if (diff <= 0) {
                if (!countdownEnded) {
                    countdownEnded = true;
                    handleCountdownEnd();
                }
                return;
            }

            // Calculate time units
            var days = Math.floor(diff / 86400);
            var remainingDiff = diff - (days * 86400);
            var hours = Math.floor(remainingDiff / 3600) % 24;
            remainingDiff -= hours * 3600;
            var minutes = Math.floor(remainingDiff / 60) % 60;
            var seconds = remainingDiff % 60;

            // Log countdown every second
            debugLog('⏱️  Countdown:', days + 'd', hours + 'h', minutes + 'm', seconds + 's', '(' + diff + 's restantes)');

            // Update the display
            updateDisplay(days, hours, minutes, seconds);
        }

        // Initial update
        updateCountdown();

        // Update the countdown every second
        var countdownInterval = setInterval(function () {
            // Check if we should stop the interval
            if (countdownEnded) {
                clearInterval(countdownInterval);
                return;
            }
            updateCountdown();
        }, 1000);
    }

    // Initialize on DOM ready
    debugLog('📋 Verificando disponibilidad de tcnData:', typeof tcnData !== 'undefined' ? 'Disponible' : 'NO disponible');
    if (typeof tcnData !== 'undefined') {
        debugLog('📦 tcnData:', tcnData);
    }

    if (document.readyState === 'loading') {
        debugLog('⏳ DOM aún cargando, esperando DOMContentLoaded...');
        document.addEventListener('DOMContentLoaded', function () {
            debugLog('✅ DOMContentLoaded disparado');
            adjustBodyPadding();
            initCountdown();
        });
    } else {
        debugLog('✅ DOM ya cargado, inicializando inmediatamente');
        adjustBodyPadding();
        initCountdown();
    }

    // Recalculate padding on window resize
    window.addEventListener('resize', adjustBodyPadding);

})();

