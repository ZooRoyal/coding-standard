/**
 * eslint config
 *
 * @licence REWE Digital GmbH
 */

module.exports = {
    extends: [
        'eslint:recommended',
        'standard'
    ],
    parserOptions: {
        ecmaVersion: 2015,
        sourceType: 'module'
    },

    overrides: [
        {
            files: '*.ts',
            plugins: [
                '@typescript-eslint'
            ],
            extends: [
                'plugin:@typescript-eslint/recommended'
            ],
            rules: {
                '@typescript-eslint/no-unused-vars': 2
            }
        }
    ],
    root: true,
    env: {
        browser: true,
        jquery: true
    },
    globals: {
        $: false,
        jQuery: true,
        console: true,
        module: true,
        window: true,
        document: true,
        require: true,
        _: true,
        pageData: true, // tracking object for CMP
        _satellite: true // Adobe tracking function
    },
    /**
     * 0 = turned off
     * 1 = warning
     * 2 = error
     */
    rules: {
        'new-cap': 1,
        'no-caller': 2,
        'no-eq-null': 2,
        indent: [
            'error',
            4
        ],
        'linebreak-style': [
            'error',
            'unix'
        ],
        quotes: [
            'error',
            'single'
        ],
        semi: 0,
        'one-var': 0,
        eqeqeq: ['error', 'smart'],
        curly: 0,
        'for-direction': 0,
        'no-tabs': 'error',
        complexity: ['error', 20], // 20 is default
        'no-undef': 2,
        'no-plusplus': 0,
        'no-underscore-dangle': 0,
        'wrap-iife': ['error', 'any'],
        'no-alert': 2,
        'no-empty-function': 2,
        'no-useless-catch': 2,
        'no-eval': 2,
        'no-implied-eval': 2,
        'no-script-url': 2,
        'no-useless-call': 2,
        'vars-on-top': 0,
        'no-console': 0,
        'no-implicit-globals': 2,
        'no-return-assign': 2,
        'no-unused-expressions': 2,
        'no-unused-vars': 2,
        'radix': 2,
        'no-trailing-spaces': 0,
        // es6
        'arrow-spacing': ['error', { before: true, after: true }],
        'no-confusing-arrow': ['error', { allowParens: false }],
        'arrow-parens': ['error', 'as-needed', { requireForBlockBody: true }],
        'no-useless-constructor': 2,
        'no-dupe-class-members': 2,
        'no-duplicate-imports': 2,
        'no-useless-computed-key': 2,
        'no-restricted-properties': 2,
        'operator-linebreak': 2,
        'no-nested-ternary': 2,
        'no-unneeded-ternary': 2,
        'standard/object-curly-even-spacing': [2, 'either'],
        'standard/array-bracket-even-spacing': [2, 'either'],
        'standard/computed-property-even-spacing': [2, 'even'],
        'standard/no-callback-literal': [2, ['cb', 'callback']]
    }
};
