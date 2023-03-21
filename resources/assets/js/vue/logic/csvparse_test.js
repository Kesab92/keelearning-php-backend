import csv from './csvparse'
import assert from 'assert'


function test_identify() {
    [
        {
            desc: 'fallback, no rows',
            csv: [
                '"a",b',  // [1, ',', true],
                '"a",b',  // [1, ',', true],
            ].join('\n'),
            min_columns: -1, exp: ',',
        },
        {
            desc: 'certain, must be comma',
            csv: [
                '"a",b',  // [1, ',', true],
                '"a",b',  // [1, ',', true],
            ].join('\n'),
            min_columns: -1, exp: ',',
        },
        {
            desc: 'certain, must be semicolon',
            csv: [
                '"a";b',  // [1, ';', true],
                '"a";b',  // [1, ';', true],
            ].join('\n'),
            min_columns: -1, exp: ';',
        },
        {
            desc: 'certain, data is invalid, use fallback',
            csv: [
                '"a",b',  // [1, ',', true],
                '"a";b',  // [1, ';', true],
            ].join('\n'),
            min_columns: -1, exp: ',',
        },
        {
            desc: 'certain, invalid input, choose dominant separator',
            csv: [
                '"a",b',  // [1, ',', true],
                '"a";b',  // [1, ';', true],
                '"a";b',  // [1, ';', true],
            ].join('\n'),
            min_columns: -1, exp: ';',
        },
        {
            desc: 'certain, invalid input, single quote, choose dominant separator',
            csv: [
                `'a',b`,  // [1, ',', true],
                `'a';b`,  // [1, ';', true],
                `'a';b`,  // [1, ';', true],
            ].join('\n'),
            min_columns: -1, exp: ';',
        },
        {
            desc: 'uncertain, only comma',
            csv: [
                'a,b',  // [1, ',', false],
                'a,b',  // [1, ',', false],
            ].join('\n'),
            min_columns: -1, exp: ',',
        },
        {
            desc: 'uncertain, only semicolon',
            csv: [
                'a;b',  // [1, ';', false],
                'a;b',  // [1, ';', false],
            ].join('\n'),
            min_columns: -1, exp: ';',
        },
        {
            desc: 'uncertain, $min_columns 2, only comma',
            csv: [
                'a,b,c',  // [2, ',', false],
                'a,b,c',  // [2, ',', false],
            ].join('\n'),
            min_columns: 2, exp: ',',
        },
        {
            desc: 'uncertain, $min_columns 2, only semicolon',
            csv: [
                'a;b;c',  // [2, ';', false],
                'a;b;c',  // [2, ';', false],
            ].join('\n'),
            min_columns: 2, exp: ';',
        },
        {
            desc: 'uncertain, $min_columns 2, same count of rows, but fewer semicolon separators',
            csv: [
                'a;b',    // [1, ';', false],
                'a,b,c',  // [2, ',', false],
            ].join('\n'),
            min_columns: 2, exp: ';',
        },
        {
            desc: 'uncertain $min_columns 2, choose dominant semicolon separator',
            csv: [
                'a;b;c',  // [2, ';', false]
                'a;b;c',  // [2, ';', false]
                'a,b,c',  // [2, ',', false]
            ].join('\n'),
            min_columns: 2, exp: ';',
        },
        {
            desc: 'uncertain $min_columns 2, choose dominant comma separator',
            csv: [
                'a,b,c',  // [2, ',', false]
                'a,b,c',  // [2, ',', false]
                'a;b;c',  // [2, ';', false]
            ].join('\n'),
            min_columns: 2, exp: ',',
        },
        {
            desc: 'uncertain, same count of rows, but fewer comma separators',
            csv: [
                'a,b',    // [1, ';', false],
                'a;b;c',  // [2, ',', false],
            ].join('\n'),
            min_columns: -1, exp: ',',
        },
    ].forEach((t) => {
        // console.log(t.desc);
        assert.strictEqual(t.exp, csv.identify(t.csv));
    });
}


function test_identify_row() {
    [
        {
            desc: 'clear kind 1, sep after esc',
            row: '"a",b,c',
            exp: {count: 1, sep: ',', sure: true},
        },
        {
            desc: 'unclear kind 2, more commas than semicolons',
            row: 'a;b,c,d',
            exp: {count: 2, sep: ',', sure: false},
        },
        {
            desc: 'unclear kind 2, same number of commas and semicolons',
            row: 'a;b,c',
            exp: {count: 1, sep: '', sure: false},
        },
        {
            desc: 'unclear kind 2b, sep before esc, more semicolons than commas',
            row: 'a;b;c,"d"',
            exp: {count: 2, sep: ';', sure: false},
        },
        {
            desc: 'unclear kind 2b, sep before esc, same number of different sep',
            row: 'a;b,"c"',
            exp: {count: 1, sep: '', sure: false},
        },
        {
            desc: 'empty input',
            row: '',
            exp: {count: 0, sep: '', sure: false},
        },
    ].forEach((t) => {
        // console.log(t.desc);
        let { count, sep, sure} = csv.identify_row(t.row);
        assert.strictEqual(t.exp.count, count);
        assert.strictEqual(t.exp.sep, sep);
        assert.strictEqual(t.exp.sure, sure);
    });
}

function test_identify_escape_char() {
    [
        {
            desc: 'single quote - okay',
            inp: `'Hello, World!', 1`,
            exp: `'`,
        },
        {
            desc: 'single quote - okay with semicolon',
            inp: `'Hello; World!'; 1`,
            exp: `'`,
        },
        {
            desc: 'double quote - okay with semicolon',
            inp: `"Hello; World!"; 1`,
            exp: `"`,
        },
        {
            desc: 'double quote - okay',
            inp: `"Hello, World!", 1`,
            exp: `"`,
        },
        {
            desc: 'single quote - detect on \\n',
            inp: `a,b,'c'''',Z'''\n`,
            exp: `'`,
        },
        {
            desc: 'double quote - detect on \\n',
            inp: `a,b,"c"""",Z"""\n`,
            exp: `"`,
        },
        {
            desc: 'single quote - detect on \\r',
            inp: `a,b,'c'''',Z'''\r\n`,
            exp: `'`,
        },
        {
            desc: 'double quote - detect on \\r',
            inp: `a,b,"c"""",Z"""\r\n`,
            exp: `"`,
        },
        {
            desc: 'fallback - empty input',
            inp: ``,
            exp: `"`,
        },
    ].forEach(t => {
        // console.log(t.desc);
        assert.strictEqual(t.exp, csv.identify_escape_char(t.inp));
    });
}


function all() {
    test_identify();
    test_identify_row();
    test_identify_escape_char();
}

all();

// module.exports = {
//     all,
// }
