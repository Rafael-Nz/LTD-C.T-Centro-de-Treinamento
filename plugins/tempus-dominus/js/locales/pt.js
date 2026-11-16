// Tempus Dominus pt-BR locale provider
(function () {
    const ptLocale = {
        name: 'pt',
        localization: {
            today: 'Hoje',
            clear: 'Limpar seleção',
            close: 'Fechar',
            selectMonth: 'Selecionar Mês',
            previousMonth: 'Mês Anterior',
            nextMonth: 'Próximo Mês',
            selectYear: 'Selecionar Ano',
            previousYear: 'Ano Anterior',
            nextYear: 'Próximo Ano',
            selectDecade: 'Selecionar Década',
            previousDecade: 'Década Anterior',
            nextDecade: 'Próxima Década',
            previousCentury: 'Século Anterior',
            nextCentury: 'Próximo Século',
            pickHour: 'Escolher Hora',
            incrementHour: 'Aumentar Hora',
            decrementHour: 'Diminuir Hora',
            pickMinute: 'Escolher Minuto',
            incrementMinute: 'Aumentar Minuto',
            decrementMinute: 'Diminuir Minuto',
            pickSecond: 'Escolher Segundo',
            incrementSecond: 'Aumentar Segundo',
            decrementSecond: 'Diminuir Segundo',
            toggleMeridiem: 'Alternar AM/PM',
            selectTime: 'Selecionar Hora',
            selectDate: 'Selecionar Data',
            dayViewHeaderFormat: { month: 'long', year: 'numeric' },
            locale: 'pt-BR',
            startOfTheWeek: 0
        }
    };

    if (typeof window !== 'undefined' && window.tempusDominus && typeof window.tempusDominus.loadLocale === 'function') {
        window.tempusDominus.loadLocale(ptLocale);
    }
})();
