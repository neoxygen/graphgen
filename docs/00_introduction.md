# Graphgen

## Graph generation for Neo4j

Need to have quickly a demo graph ? Helping people on forums and you want to quickly prototype an approximate related
to the question ? Want to show the power of Neo4j ?

`Graphgen` is what you're looking for !


### What is it ?

Graphgen aims to ease the generation of based on `Cypher` patterns, and will also generate real world values for nodes
and relationships properties, thanks to the `faker` library.

### Rapid prototyping

You need to write only a few lines to generate complex graphs.

### Quick how-to

The documentation is in write up, you can find here a quick overview of the possibilites.

#### Defining your nodes

First you start by defining a node and assigning an identifier, a label and the amount of nodes you would like to create :

```cypher
(p:Person *35)
```

This will create 35 nodes with the `Person` label.

Note:

- The identifier is optional, but handy for relationships pattern.
- The start (*) before the desired amount and the amount are mandatory

#### Defining relationships

Defining relationships is as easy as defining nodes and the syntax try to stay close to the `Cypher Query Language`.

You need to define the `type` of the relationship and the cardinality :

```cypher
(p:Person *35)
(c:Company *10)
(p)-[:KNOWS *n..n]->(p)
(p)-[:WORKS_AT *]->(c)
```

This will create 35 `Person` nodes, 10 `Company` nodes and a relationship between persons and companies (here a person works
for only on company, but a company can have multiple persons working for them) and also multiple relationships between persons.



You can see in the above example the reuse of identifiers.

#### Defining properties

Having such a graph schema does not match the reality, in fact `Persons` have a firstname, a lastname and a dateOfBirth and
`Companies` have a name and a description.

You can add properties to your nodes and relationships, real world name values will be generated for you.

Currently, a non exhaustive list is available at the end of this page.

If you want to know the available faker types names that you can use you should look at the README of the
[faker library](https://github.com/fzaninotto/faker).

The documentation will be improved in the next days with the list of available types, also graphgen will provide their own types.

To define a property, you should give it a key, let's say `firstname` , then you assign it a type, here `firstName`.

```cypher
(p:Person {firstname: firstName} *35)
(c:Company {name: company, description: catchPhrase} *10)
```

If you need to use a type that accept arguments, you'll need to embed the type and arguments between new { } .

```cypher
(p:Person {firstname: firstName, birhdate: {dateOfBirth: ["-65 years", "-18 years"] }} *35)
```

For the example here, people are working for a company, so I wanted to make sure their date of birth is in a legal age for
working and before the retiring age (currently 65 in Belgium).

The same applies for relationships.

### Other features

- Live import to any accessible database

- Export to GraphJSON format file

- Export to Cypher Queries Import file


---

#### Maintainers

This project is an initiative of [Christophe Willemsen](https://twitter.com/ikwattro) .

#### Contribute

All comments, issues, and requests can be made in the respective Github repositories.

The code of the generator, `Neogen` is opensource and available here : https://github.com/neoxygen/neogen

The code of the site is available here : https://github.com/neoxygen/graphgen

#### Thank you

Special thanks to [Michaël Hunger](https://twitter.com/mesirii) for his help and insights.

Thanks to all the people involved in the [Neo4J](http://neo4j.org) community.

---

### Faker types

Non exhaustive list of faker types :

#### randomDigit

```
{position: randomDigit}

// 3
```

#### randomDigitNotNull

```
{position: randomDigitNotNull}

// 5
```

#### randomNumber

Parameters : `nbDigits = null`

```
{amount: randomNumber}
// 5251435546

{amount: {randomNumber: [3]}}
// 436
```

#### randomFloat

Parameters : `maxDecimals = null`, `min = 0`, `max = null`

```
{average: randomFloat}

// 49.4529
```

#### randomLetter

```
{letter: randomLetter}

// c
```

#### word

```
{before_die: word}

// argh
```

#### sentence

Parameters : `nbWords = 6`

```
{status: sentence}

// Sit vitae voluptas
```

#### firstName, lastName

```
{firstname: lastName, lastname: lastName}

// Wolfgang Rodriguez
```

#### company

```
{name: company}

// Rescue Limited
```

#### catchPhrase

```
{description: catchPhrase}

// Electronic services
```

#### unixTime

Parameters : `max = "now"`

```
{created_at: unixTime}

// 587818113
```
Parameters : `max = "now"`

#### dateTime

```
{since: dateTime}

// 2014-10-10 13:34:56
```

#### dateTimeBetween

Parameters : `start = "-30 years"`, `end = "now"`

```
{date_of_birth: {dateTimeBetween: ["-65 years", "-18 years"]}}

// 1960-10-19 20:13:11
```

---




