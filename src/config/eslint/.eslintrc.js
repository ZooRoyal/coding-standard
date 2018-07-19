module.exports = {
    "extends": [
        "eslint:recommended",
        "eslint-config-crockford"
    ],
    "plugins": [
        "m99coder"
    ],
    "root": true,
    "env": {
        "browser": true,
        "jquery": true,
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
        "eqeqeq": ["error", "always", {"null": "ignore"}],
        "curly": "error",
        "for-direction": "error",
        "no-tabs": "error",
        "complexity": ["error", 20], //20 is default
        "no-undef": "off",
        "no-plusplus": ["error", {"allowForLoopAfterthoughts": true}],
        "no-underscore-dangle": "off",
        "vars-on-top": "error",
        "m99coder/vars-on-top": [2, {"forStatement": true, "forInStatement": true, "forOfStatement": true}],
        "wrap-iife": ["error", "inside"]
    }
};
