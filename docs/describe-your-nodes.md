# Describe your nodes

The node description contains 4 parts :

* The identifier
* The labels
* The properties
* The nodes count

![Imgur](http://i.imgur.com/nkfzAes.png)

### The identifier

Like in the `Cypher` spec, the identifier helps to reuse your descriptions further in the pattern. It accepts only
alphanumeric characters.

NB: The identifier is mandatory.

### The labels

A label is mandatory, you can if you want describe more than one label :

```
(person:Person:User:Speaker *35)
```

### The properties

You can add node properties and get them automatically generated with random fake values. The application uses the original [Faker](https://github.com/fzaninotto/faker) 
library combined with some extra custom providers from the [ikwattro-faker-extra](https://github.com/ikwattro/faker-extra) package.

The properties are defined using the inline YAML format, where keys are the property keys of your nodes and values are the faker type you want to generate.

Nothing better than an example, a Person should have a firstname and a lastname. The corresponding faker types are `firstName` and `lastName` :

```
(person:Person {firstname: firstName, lastname: lastName} *35)
```

For the complete list of available types, check out the `Property Types` reference of the documentation.

### The nodes count

The count have the form `*35`. You can omit it and have a default count of 1.

Please not that playing with too much nodes can have impact on your browser stability, we recommended that you stay below a limit of 1000 nodes.


