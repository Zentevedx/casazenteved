import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import "dayjs/locale/es";

// Configurar dayjs
dayjs.extend(utc);
dayjs.locale("es");

export function useFormatters() {
    /**
     * Formatea un número como moneda (Bolivianos).
     * @param {Number|String} value 
     * @returns {String} String formateado (ej. "1,234.00")
     */
    const formatCurrency = (value) => {
        if (!value) return "0.00";
        return Number(value).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };

    /**
     * Trunca o formatea el monto sin decimales innecesarios, o igual que la moneda
     */
    const formatMonto = (valor) => {
        if (!valor) return "0";
        return Number(valor).toLocaleString('en-US');
    };

    /**
     * Formatea una fecha en formato corto estándar.
     * @param {String|Date} date 
     * @param {String} mask Formato, por defecto "DD/MM/YYYY"
     * @returns {String} Fecha formateada
     */
    const formatDate = (date, mask = 'DD/MM/YYYY') => {
        if (!date) return '—';
        return dayjs(date).utc().format(mask);
    };

    /**
     * Renderiza una etiqueta de tiempo ("1 año, 2 meses") basado en días.
     */
    const formatTimeLabel = (dias, meses) => {
        if (!dias) return '0 días';
        if (meses >= 12) {
            const anios = Math.floor(meses / 12);
            const mesesRestantes = meses % 12;
            return `${anios} año${anios > 1 ? 's' : ''}${mesesRestantes > 0 ? `, ${mesesRestantes} mes${mesesRestantes > 1 ? 'es' : ''}` : ''}`;
        }
        if (meses > 0) return `${meses} mes${meses > 1 ? 'es' : ''}`;
        return `${dias} días`;
    };

    /**
     * Retorna clases CSS según la urgencia (días). Usado típicamente en remates o cobros.
     */
    const urgencyColor = (dias) => {
        if (dias > 365) return 'bg-red-600 text-white shadow-sm ring-1 ring-red-700/50';
        if (dias >= 180) return 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400';
        if (dias >= 90) return 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400';
        return 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400';
    };

    return {
        formatCurrency,
        formatMonto,
        formatDate,
        formatTimeLabel,
        urgencyColor,
        dayjs // Exportado para usos avanzados si fuera necesario
    };
}
