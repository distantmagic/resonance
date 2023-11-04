module.exports = {
    env: {
        'es6': true,
    },
    extends: [
        'eslint:recommended',
        'plugin:@typescript-eslint/recommended-type-checked',
    ],
    parser: '@typescript-eslint/parser',
    parserOptions: {
        project: true,
        tsconfigRootDir: __dirname,
    },
    plugins: [
        "import",
        "simple-import-sort",
        '@typescript-eslint',
    ],
    root: true,
    overrides: [
        {
            files: ["*.ts", "*.mts", "*.cts", "*.tsx"],
            rules: {
                "@typescript-eslint/array-type": [
                    "error",
                    {
                        "default": "generic",
                    },
                ],
                "@typescript-eslint/class-literal-property-style": "error",
                "@typescript-eslint/consistent-generic-constructors": "error",
                "@typescript-eslint/consistent-indexed-object-style": [
                    "error",
                    "index-signature",
                ],
                "@typescript-eslint/explicit-function-return-type": "error",
                "@typescript-eslint/explicit-member-accessibility": "error",
                "@typescript-eslint/explicit-module-boundary-types": "error",
                "@typescript-eslint/member-delimiter-style": "error",
                "@typescript-eslint/member-ordering": [
                    "error",
                    {
                        "default": {
                            "memberTypes": [
                                "abstract-field",
                                "abstract-method",
                                "public-static-field",
                                "protected-static-field",
                                "private-static-field",
                                "public-field",
                                "protected-field",
                                "private-field",
                                "constructor",
                                "signature",
                                "public-method",
                                "protected-method",
                                "private-method",
                            ],
                            "order": "alphabetically",
                        },
                    }
                ],
                "@typescript-eslint/method-signature-style": ["error", "method"],
                "@typescript-eslint/no-confusing-non-null-assertion": "error",
                "@typescript-eslint/no-confusing-void-expression": "error",
                "@typescript-eslint/no-invalid-void-type": "error",
                "@typescript-eslint/no-import-type-side-effects": "error",
                "@typescript-eslint/no-non-null-assertion": "error",
                "@typescript-eslint/no-unnecessary-boolean-literal-compare": "error",
                "@typescript-eslint/no-unnecessary-type-arguments": "error",
                "@typescript-eslint/no-unnecessary-type-assertion": "error",
                "@typescript-eslint/no-useless-empty-export": "error",
                "@typescript-eslint/prefer-for-of": "error",
                "@typescript-eslint/prefer-function-type": "error",
                "@typescript-eslint/prefer-optional-chain": "error",
                "@typescript-eslint/prefer-readonly": "error",
                "@typescript-eslint/prefer-regexp-exec": "error",
                "@typescript-eslint/prefer-return-this-type": "error",
                "@typescript-eslint/prefer-string-starts-ends-with": "error",
                "@typescript-eslint/prefer-ts-expect-error": "error",
                "@typescript-eslint/require-array-sort-compare": "error",
                "@typescript-eslint/sort-type-constituents": [
                    "error",
                    {
                        "groupOrder": [
                          'nullish',
                          'named',
                          'keyword',
                          'operator',
                          'literal',
                          'function',
                          'import',
                          'conditional',
                          'object',
                          'tuple',
                          'intersection',
                          'union',
                        ],
                    }
                ],
                "@typescript-eslint/switch-exhaustiveness-check": "error",
                "@typescript-eslint/unified-signatures": "error",
            },
        }
    ],
    rules: {
        "simple-import-sort/imports": [
            "error",
            {
                // regular imports, then types
                "groups": [
                    ["^\\u0000"],
                    ["^node:"],
                    ["^@?\\w"],
                    ["^\\."],
                    ["(?<!\\u0000)$"],
                    ["^@?\\w.*\\u0000$"],
                    ["(?<=\\u0000)$"],
                    ["^node:.*\\u0000$"],
                    ["^\\..*\\u0000$"],
                ],
            }
        ],
        "simple-import-sort/exports": "error",
        "import/first": "error",
        "import/newline-after-import": "error",
        "import/no-duplicates": "error"
    },
};
