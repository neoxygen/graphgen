# Properties reference

Properties are described in an inline YAML format, where keys are property keys of your nodes and values are the faker type you want to use :

```
{firstname: firstName}
```

For a reference of the available faker types, browse the types by section in the properties reference dropdown.

### Passing arguments

For some types, you need to pass arguments. You then need to define your type as an array :
 
```
{dateOfBirth: {dateTimeBetween: ["-50 years", "-18 years"] }}
```

### Passing an array as argument

An example says more than words :

```
{level: {randomElement:[[-1,1,2,3]] }}
```

You may want to look at the new `Node Type` feature in the documentation.