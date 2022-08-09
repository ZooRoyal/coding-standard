/**
 * Eslint config.
 *
 * @license REWE Digital GmbH
 */

module.exports = {
    extends: [
        'eslint:recommended',
        'standard',
        'plugin:jest/recommended',
        'plugin:jest/style',
    ],
    parserOptions: {
        ecmaVersion: 2016,
        sourceType: 'module',
        project: './tsconfig.json',
    },
    plugins: [
        'jest',
        'jsdoc',
    ],
    overrides: [
        {
            files: ['*.ts'],
            plugins: [
                '@typescript-eslint',
            ],
            extends: [
                'plugin:@typescript-eslint/recommended',
                'plugin:@stencil/recommended',
            ],
            rules: {
                '@typescript-eslint/no-unused-vars': 2,
                'no-useless-constructor': 'off',
                'jsdoc/require-returns': 0,
                'jsdoc/require-param': 0,
            },
        },
        {
            files: ['*.tsx'],
            plugins: [
                '@typescript-eslint',
            ],
            extends: [
                'plugin:@typescript-eslint/recommended',
                'plugin:@stencil/recommended',
            ],
            rules: {
                'react/jsx-no-bind': 0,
                'jsdoc/check-tag-names': ['error', {
                    definedTags: [
                        'widgetName',
                        'widgetIcon',
                        'widgetFieldType',
                        'widgetFieldLabel',
                        'widgetFieldSupportText',
                        'widgetFieldHelpTitle',
                        'widgetFieldHelpText',
                        'widgetFieldDefaultValue'
                    ],
                }],
            },
        },
    ],
    root: true,
    env: {
        browser: true,
        jquery: true,
        'jest/globals': true,
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
        _satellite: true, // Adobe tracking function
    },
    /**
     * 0 = turned off
     * 1 = warning
     * 2 = error.
     */
    rules: {
        'new-cap': 1,
        'no-caller': 2,
        'no-eq-null': 2,
        indent: [
            'error',
            4,
        ],
        'linebreak-style': [
            'error',
            'unix',
        ],
        quotes: [
            'error',
            'single',
        ],
        'max-len': [
            'error', {
                code: 130,
                ignoreTemplateLiterals: true,
                ignoreUrls: true,
                ignoreTrailingComments: true,
                ignoreRegExpLiterals: true,
            },
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
        radix: 2,
        'no-trailing-spaces': 0,
        'require-jsdoc': ['error', {
            require: {
                FunctionDeclaration: true,
                MethodDefinition: true,
                ClassDeclaration: true,
                ArrowFunctionExpression: true,
                FunctionExpression: true,
            },
        }],
        'comma-dangle': ['error', {
            arrays: 'always-multiline',
            objects: 'always-multiline',
            imports: 'always-multiline',
            exports: 'always-multiline',
            functions: 'never',
        }],
        'space-before-function-paren': ['error', {
            anonymous: 'always',
            named: 'never',
            asyncArrow: 'always',
        }],
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
        'standard/no-callback-literal': [2, ['cb', 'callback']],
        // jest
        'jest/no-disabled-tests': 'warn',
        'jest/no-if': 'error',
        'jest/no-focused-tests': 'error',
        'jest/no-identical-title': 'error',
        'jest/prefer-to-have-length': 'warn',
        'jest/valid-expect': 'error',
        // jsdoc,
        'jsdoc/check-access': 0,
        'jsdoc/check-alignment': 2,
        'jsdoc/check-examples': 2,
        'jsdoc/check-indentation': 0,
        'jsdoc/check-line-alignment': 2,
        'jsdoc/check-param-names': 2,
        'jsdoc/check-property-names': 2,
        'jsdoc/check-syntax': 2,
        'jsdoc/check-tag-names': 2,
        'jsdoc/check-types': 2,
        'jsdoc/check-values': 0,
        'jsdoc/empty-tags': 2,
        'jsdoc/implements-on-classes': 2,
        'jsdoc/match-description': 0,
        'jsdoc/newline-after-description': 2,
        'jsdoc/no-bad-blocks': 0,
        'jsdoc/no-defaults': 0,
        'jsdoc/no-types': 0,
        'jsdoc/no-undefined-types': 0,
        'jsdoc/require-description': 2,
        'jsdoc/require-description-complete-sentence': 2,
        'jsdoc/require-example': 0,
        'jsdoc/require-file-overview': 0,
        'jsdoc/require-hyphen-before-param-description': 0,
        'jsdoc/require-jsdoc': 2,
        'jsdoc/require-param': 2,
        'jsdoc/require-param-description': 0,
        'jsdoc/require-param-name': 2,
        'jsdoc/require-param-type': 2,
        'jsdoc/require-property': 0,
        'jsdoc/require-property-description': 0,
        'jsdoc/require-property-name': 0,
        'jsdoc/require-property-type': 0,
        'jsdoc/require-returns': 2,
        'jsdoc/require-returns-check': 2,
        'jsdoc/require-returns-description': 2,
        'jsdoc/require-returns-type': 2,
        'jsdoc/valid-types': 2,
    },
};
