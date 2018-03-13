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
        "jquery": true
    },
    "globals": {
        "$": false,
        "jQuery": true,
        "console": true,
        "module": true,
        "window": true,
        "document": true,
        "require": true,
        "_": true // underscorejs
    },
    "rules": {
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
        "vars-on-top": "off", //temp set to warn cause it does not have options for FOR and WHILE loops // https://github.com/eslint/eslint/issues/2517
        "m99coder/vars-on-top": [2, {"forStatement": true, "forInStatement": false, "forOfStatement": false}] // https://www.npmjs.com/package/eslint-plugin-m99coder
    }
};

