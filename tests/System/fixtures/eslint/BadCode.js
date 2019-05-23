let test = name => {
    let b;
    b = 1;
    const c = 2;
    return "hurra" +  name  +  " " + b + c;
};
for (let i = 0; i < 10; i++) {
    test("dudel");
}

//Arrow Functions
let odds  = evens.map(v => v + 1)
let pairs = evens.map(v => ({ even: v, odd: v + 1 }))
let nums  = evens.map((v, i) => v + i)

//Default Params
let defaultParams = (x, y = 7, z = 42) => {
    return x + y + z
}
defaultParams(1) === 50;

//Extended Parameter Handling
//Rest Parameter
let restParams = (x, y, ...a) => {
    return (x + y) * a.length
};
restParams(1, 2, "hello", true, 7) === 9;

//Spread Operators
var params = [ "hello", true, 7 ];
var other = [ 1, 2, ...params ]; // [ 1, 2, "hello", true, 7 ]

let spreadInFunc = (x, y, ...a) => {
    return (x + y) * a.length;
};
spreadInFunc(1, 2, ...params) === 9;


//Literals
let customer = { name: "Foo" };
let card = { amount: 7, product: "Bar", unitprice: 42 };
let message = `Hello ${customer.name},
want to buy ${card.amount} ${card.product} for
a total of ${card.amount * card.unitprice} bucks?`;

//Destructuring: Object Matching, Deep Matching
var { op: a, lhs: { op: b }, rhs: c } = getASTNode()

//Destructuring Assignment: Object And Array Matching, Default Values
let obj = { a: 1 };
let list = [ 1 ];
let { v, w = 2 } = obj;
let [ x, y = 2 ] = list;

//Destructuring Assignment: Parameter Context Matching
function f ([ name, val ]) {
    console.log(name, val);
}
function g ({ name: n, val: v }) {
    console.log(n, v);
}
function h ({ name, val }) {
    console.log(name, val);
}
f([ "bar", 42 ]);
g({ name: "foo", val:  7 });
h({ name: "bar", val: 42 });

//Classes
class Shape {
    constructor (id, x, y) {
        this.id = id
        this.move(x, y)
    }
    move (x, y) {
        this.x = x
        this.y = y
    }
}
class Rectangle extends Shape {
    constructor (id, x, y, width, height) {
        super(id, x, y)
        this.width  = width
        this.height = height
    }
    static defaultRectangle () {
        return new Rectangle("default", 0, 0, 100, 100)
    }
    set width(width)  {
        this._width = width
    }
    get width() {
        return this._width
    }
    get area() {
        return this._width * this._height;
    }
}
let r = new Rectangle(50, 20);
r.area === 1000;
class Circle extends Shape {
    constructor (id, x, y, radius) {
        super(id, x, y);
        this.radius = radius
    }
}

let defRectangle = Rectangle.defaultRectangle();
let defCircle    = Circle.defaultCircle();

//Classes: Class Inheritance, From Expressions
var aggregation = (baseClass, ...mixins) => {
    let base = class _Combined extends baseClass {
        constructor (...args) {
            super(...args)
            mixins.forEach((mixin) => {
                mixin.prototype.initializer.call(this)
            })
        }
    };
    let copyProps = (target, source) => {
        Object.getOwnPropertyNames(source)
            .concat(Object.getOwnPropertySymbols(source))
            .forEach((prop) => {
                if (prop.match(/^(?:constructor|prototype|arguments|caller|name|bind|call|apply|toString|length)$/))
                    return;
                Object.defineProperty(target, prop, Object.getOwnPropertyDescriptor(source, prop))
            })
    };
    mixins.forEach((mixin) => {
        copyProps(base.prototype, mixin.prototype)
        copyProps(base, mixin)
    });
    return base
};

class Colored {
    initializer ()     { this._color = "white" }
    get color ()       { return this._color }
    set color (v)      { this._color = v }
}

class ZCoord {
    initializer ()     { this._z = 0 }
    get z ()           { return this._z }
    set z (v)          { this._z = v }
}

class Structure {
    constructor (x, y) { this._x = x; this._y = y }
    get x ()           { return this._x }
    set x (v)          { this._x = v }
    get y ()           { return this._y }
    set y (v)          { this._y = v }
}

class AltRectangle extends aggregation(Structure, Colored, ZCoord) {}

var rect = new AltRectangle(7, 42);
rect.z     = 1000;
rect.color = "red";
console.log(rect.x, rect.y, rect.z, rect.color);

//Symbols
Symbol("foo") !== Symbol("foo");
let object = {};
JSON.stringify(object); // {}
Object.keys(object); // []
Object.getOwnPropertyNames(object); // []
Object.getOwnPropertySymbols(object); // [ foo, bar ]

//Promisses
function msgAfterTimeout (msg, who, timeout) {
    return new Promise((resolve, reject) => {
        setTimeout(() => resolve(`${msg} Hello ${who}!`), timeout)
    })
}
msgAfterTimeout("", "Foo", 100).then((msg) =>
    msgAfterTimeout(msg, "Bar", 200)
).then((msg) => {
    console.log(`done after 300ms:${msg}`)
});

//Modules: Value Export Default & Wildcard
export default BadBadCode;
