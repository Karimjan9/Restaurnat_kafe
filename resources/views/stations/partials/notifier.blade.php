@once
    <script>
        (() => {
            const station = @json($station);
            const branchId = @json($branchId);
            const bootKey = `__restaurantPosStationNotifier_${station}`;

            if (window[bootKey]) {
                return;
            }

            window[bootKey] = true;

            const seenEvents = new Set();

            const playSignal = () => {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;

                if (!AudioContextClass) {
                    return;
                }

                const context = window.__restaurantPosAudioContext || new AudioContextClass();
                window.__restaurantPosAudioContext = context;

                const runTone = () => {
                    const timeline = [0, 0.18, 0.32];
                    const frequencies = [880, 660, 990];

                    frequencies.forEach((frequency, index) => {
                        const oscillator = context.createOscillator();
                        const gain = context.createGain();
                        const startAt = context.currentTime + timeline[index];
                        const endAt = startAt + 0.12;

                        oscillator.type = 'sine';
                        oscillator.frequency.setValueAtTime(frequency, startAt);
                        gain.gain.setValueAtTime(0.0001, startAt);
                        gain.gain.exponentialRampToValueAtTime(0.18, startAt + 0.02);
                        gain.gain.exponentialRampToValueAtTime(0.0001, endAt);

                        oscillator.connect(gain);
                        gain.connect(context.destination);
                        oscillator.start(startAt);
                        oscillator.stop(endAt);
                    });
                };

                if (context.state === 'suspended') {
                    context.resume().then(runTone).catch(() => null);
                    return;
                }

                runTone();
            };

            window.addEventListener('restaurant-pos:operations-updated', (event) => {
                const payload = event.detail ?? {};

                if (payload.type !== 'station.order.queued' || payload.station !== station) {
                    return;
                }

                if (branchId && payload.branch_id && Number(payload.branch_id) !== Number(branchId)) {
                    return;
                }

                const eventKey = [payload.type, payload.station, payload.order_id, payload.sent_at].join(':');

                if (seenEvents.has(eventKey)) {
                    return;
                }

                seenEvents.add(eventKey);
                playSignal();
            });
        })();
    </script>
@endonce
