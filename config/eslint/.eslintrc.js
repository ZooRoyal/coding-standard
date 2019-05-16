const path = require("path");
module.exports = {
    "extends": [
        "eslint:recommended",
        "standard",
        "eslint-config-crockford",
        "plugin:import/errors",
        "plugin:import/warnings"
    ],
    "plugins": [
        "m99coder",
        "standard",
        "import"
    ],
    "parserOptions": {
        "ecmaVersion": 6,
        "sourceType": "module",
        "ecmaFeatures": {
            "jsx": false,
            "impliedStrict": true
        },
        "useJSXTextNode": true
    },
    "root": true,
    "env": {
        "browser": true,
        "jquery": true,
        "es6": true,
        "jest": true,
        "node": true,
        "builtin": true
    },
    "globals": {
        "$": false,
        "jQuery": true,
        "console": true,
        "module": true,
        "window": true,
        "document": true,
        "require": true,
        "_": true
    },
    "rules": {
        "new-cap": 2,
        "no-caller": 2,
        "no-eq-null": 2,
        "indent": [
            "error",
            4
        ],
        "linebreak-style": [
            "error",
            "unix"
        ],
        "quotes": [
            "error",
            "double"
        ],
        "semi": [
            "error",
            "always"
        ],
        "one-var": "off",
        "eqeqeq": ["error", "smart"],
        "curly": "error",
        "for-direction": "error",
        "no-tabs": "error",
        "complexity": ["error", 20], //20 is default
        "no-undef": "off",
        "no-plusplus": ["error", {"allowForLoopAfterthoughts": true}],
        "no-underscore-dangle": "off",
        "wrap-iife": ["error", "inside"],
        "no-alert": "error",
        "no-empty-function": "error",
        "no-useless-catch": "error",
        "no-eval": "error",
        "no-implied-eval": "error",
        "no-script-url": "error",
        "no-useless-call": "error",
        "vars-on-top": "off",
        //es6
        "arrow-spacing": ["error", { "before": true, "after": true }],
        "no-confusing-arrow": ["error", {"allowParens": false }],
        "allow-parens": ["as-needed", { "requireForBlockBody": true }],
        "no-useless-constructor": "error",
        "no-dupe-class-members": "error",
        "no-duplicate-imports": "error",
        "no-useless-computed-key": "error",
        "no-restricted-properties": "error",
        "operator-linebreak": "error",
        "no-nested-ternary": "error",
        "no-unneeded-ternary": "error",
        //plugin:standard
        'standard/object-curly-even-spacing': [2, "either"],
        'standard/array-bracket-even-spacing': [2, "either"],
        'standard/computed-property-even-spacing': [2, "even"],
        'standard/no-callback-literal': [2, ["cb", "callback"]],
        //plugin:import
        "import/no-unresolved": [2, {commonjs: true, amd: true}],
        "import/named": 2,
        "import/namespace": 2,
        "import/default": 2,
        "import/export": 2
    }
};
