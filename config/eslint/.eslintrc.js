module.exports = {
    "extends": [
        "eslint:recommended",
        "eslint-config-crockford"
    ],
    "plugins": [
        "m99coder"
    ],
    "parserOptions": {
        "ecmaVersion": 6,
        "ecmaFeatures": {
            "impliedStrict": true
        }
    },
    "root": true,
    "env": {
        "browser": true,
        "jquery": true,
        "es6": true,
        "jasmine": true,
        "node": true,
        "mocha": true,
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
        "no-unneeded-ternary": "error"
    }
};
