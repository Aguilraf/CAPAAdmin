export const formatCurrency = (amount) => {
    const val = parseFloat(amount) || 0;
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(val);
};
