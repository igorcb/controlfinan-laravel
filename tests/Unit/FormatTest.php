<?php

use App\Support\Format;

describe('Format', function () {
    it('formats currency like number_to_currency in pt-BR', function () {
        expect(Format::currency('1234.5'))->toBe('R$ 1.234,50')
            ->and(Format::currency('-12.5'))->toBe('-R$ 12,50')
            ->and(Format::currency(-0.0))->toBe('R$ 0,00');
    });

    it('prints decimals like Ruby BigDecimal', function () {
        expect(Format::decimalText('150.00'))->toBe('150.0')
            ->and(Format::decimalText('12.50'))->toBe('12.5')
            ->and(Format::decimalText('0.05'))->toBe('0.05')
            ->and(Format::decimalText(null))->toBe('');
    });
});
