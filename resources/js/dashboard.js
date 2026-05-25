window.dashboardMonitor = ({ initialData, dataUrl }) => ({
    dashboard: initialData,
    dataUrl,
    renewalYear: initialData?.renewal_chart?.selected_year ? String(initialData.renewal_chart.selected_year) : '',
    currentTime: null,
    clockTimer: null,
    refreshTimer: null,
    realtimeDebounceTimer: null,
    isRefreshing: false,

    init() {
        this.setDashboard(this.dashboard);

        this.clockTimer = window.setInterval(() => {
            if (this.currentTime) {
                this.currentTime = new Date(this.currentTime.getTime() + 1000);
            }
        }, 1000);

        this.bindDashboardRealtime();

        // Polling desactivado - Migrado a Realtime (ver bindDashboardRealtime)

        window.addEventListener('beforeunload', () => {
            window.clearInterval(this.clockTimer);
            window.clearInterval(this.refreshTimer);
            if (this.realtimeDebounceTimer) {
                window.clearTimeout(this.realtimeDebounceTimer);
            }
        }, { once: true });
    },

    bindDashboardRealtime() {
        if (!window.Echo) {
            return;
        }

        const pairs = [
            ['weapons.updates', 'WeaponChanged'],
            ['clients.updates', 'ClientChanged'],
            ['transfers.updates', 'TransferChanged'],
            ['assignments.updates', 'AssignmentChanged'],
            ['maps.updates', 'MapDataChanged'],
            ['posts.updates', 'PostChanged'],
            ['workers.updates', 'WorkerChanged'],
        ];

        const scheduleRefresh = () => {
            if (document.visibilityState === 'hidden') {
                return;
            }
            if (this.realtimeDebounceTimer) {
                window.clearTimeout(this.realtimeDebounceTimer);
            }
            this.realtimeDebounceTimer = window.setTimeout(() => {
                this.realtimeDebounceTimer = null;
                this.refreshMetrics();
            }, 400);
        };

        pairs.forEach(([channel, eventName]) => {
            try {
                window.Echo.private(channel).listen(eventName, scheduleRefresh);
            } catch (error) {
                console.warn('No se pudo suscribir al canal en tiempo real.', channel, error);
            }
        });
    },

    async refreshMetrics() {
        if (this.isRefreshing) {
            return;
        }

        this.isRefreshing = true;

        try {
            const response = await window.axios.get(this.dataUrl, {
                params: this.renewalYear ? { renewal_year: this.renewalYear } : {},
            });
            this.setDashboard(response.data);
        } catch (error) {
            console.error('No se pudo actualizar el dashboard.', error);
        } finally {
            this.isRefreshing = false;
        }
    },

    setDashboard(payload) {
        this.dashboard = payload;
        this.renewalYear = payload?.renewal_chart?.selected_year ? String(payload.renewal_chart.selected_year) : '';
        this.currentTime = payload?.as_of ? new Date(payload.as_of) : new Date();
    },

    async applyRenewalYear(year) {
        this.renewalYear = String(year);
        await this.refreshMetrics();
    },

    formatNumber(value) {
        return new Intl.NumberFormat('es-CO').format(value ?? 0);
    },

    formattedAsOf() {
        if (!this.currentTime) {
            return '';
        }

        return new Intl.DateTimeFormat('es-CO', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
            timeZone: 'America/Bogota',
        }).format(this.currentTime);
    },

    barWidth(value, max, min = 10) {
        if (!value || !max) {
            return 0;
        }

        return Math.max(min, Math.round((value / max) * 100));
    },

    columnHeight(value, max, minVisible = 11) {
        const numericValue = Number(value ?? 0);
        const numericMax = Number(max ?? 0);

        if (!numericValue || !numericMax) {
            return 0;
        }

        const proportionalHeight = (numericValue / numericMax) * 100;

        return Math.max(minVisible, Math.min(100, Number(proportionalHeight.toFixed(2))));
    },

    renewalSegments() {
        return [
            { key: 'vigente', label: 'Vigente' },
            { key: 'preventiva', label: 'Preventiva' },
            { key: 'por_vencer', label: 'Por vencer' },
            { key: 'vencido', label: 'Vencido' },
            { key: 'incautada', label: 'Incautación en trámite' },
        ];
    },

    renewalSegmentBackground(segmentKey) {
        const backgrounds = {
            vigente: 'linear-gradient(180deg, #22c55e 0%, #16a34a 45%, #15803d 100%)',
            preventiva: 'linear-gradient(180deg, #fbbf24 0%, #d97706 48%, #b45309 100%)',
            por_vencer: 'linear-gradient(180deg, #fb923c 0%, #ea580c 48%, #c2410c 100%)',
            vencido: 'linear-gradient(180deg, #f87171 0%, #dc2626 48%, #b91c1c 100%)',
            incautada: 'linear-gradient(180deg, #9f1239 0%, #881337 48%, #701a2f 100%)',
        };

        return backgrounds[segmentKey] ?? backgrounds.vigente;
    },

    normalizeRenewalMonth(item) {
        const vigente = Number(item?.vigente ?? 0) + Number(item?.sin_novedad ?? 0);
        const preventiva = Number(item?.preventiva ?? 0);
        const porVencer = Number(item?.por_vencer ?? 0);
        const vencido = Number(item?.vencido ?? 0);
        const incautada = Number(item?.incautada ?? 0);
        const total = Number(item?.total ?? 0) || (vigente + preventiva + porVencer + vencido + incautada);

        return {
            vigente,
            preventiva,
            por_vencer: porVencer,
            vencido,
            incautada,
            total,
        };
    },

    renewalSegmentCount(item, segmentKey) {
        const normalized = this.normalizeRenewalMonth(item);

        return Number(normalized[segmentKey] ?? 0);
    },

    renewalStackHeight(item, max) {
        const numericTotal = this.normalizeRenewalMonth(item).total;
        const numericMax = Number(max ?? 0);

        if (!numericTotal || !numericMax) {
            return 0;
        }

        const proportionalHeight = (numericTotal / numericMax) * 88;

        return Number(Math.min(88, proportionalHeight).toFixed(2));
    },

    renewalSegmentShareInStack(value, total) {
        const numericValue = Number(value ?? 0);
        const numericTotal = Number(total ?? 0);

        if (!numericValue || !numericTotal) {
            return 0;
        }

        return Number(((numericValue / numericTotal) * 100).toFixed(2));
    },

    renewalSegmentLabelInside(value, total, stackHeightPercent) {
        const share = this.renewalSegmentShareInStack(value, total);

        return Number(value ?? 0) > 0
            && Number(stackHeightPercent ?? 0) >= 14
            && share >= 22;
    },

    renewalSegmentLabelAbove(value, total, stackHeightPercent) {
        const share = this.renewalSegmentShareInStack(value, total);

        return Number(value ?? 0) > 0 && (
            Number(stackHeightPercent ?? 0) < 14
            || share < 22
        );
    },

    renewalSegmentShowsLabel(value, total, stackHeightPercent) {
        return this.renewalSegmentLabelInside(value, total, stackHeightPercent)
            || this.renewalSegmentLabelAbove(value, total, stackHeightPercent);
    },

    renewalBarStyle(item, segmentKey, max) {
        const normalized = this.normalizeRenewalMonth(item);
        const value = Number(normalized[segmentKey] ?? 0);
        const height = this.renewalSegmentShareInStack(value, normalized.total);
        const background = this.renewalSegmentBackground(segmentKey);

        return `height: ${height}%; background: ${background}`;
    },
});
