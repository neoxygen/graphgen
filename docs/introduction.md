# Introduction

Need to have quickly a demo graph ? Helping people on forums and you want to quickly prototype an approximate related
to the question ? Want to show the power of Neo4j ?

`Graphgen` is what you're looking for !

---


## What is it ?

Graphgen is a graph generation engine based on the `Cypher` specification. It creates nodes and relationships in accordance
with your schema definition and can also generate fake property values.

---

## Rapid prototyping

You need to write only a few lines to generate complex graphs.

Look at the following example :

```cypher
(person:Person {name: fullName} *35)-[:WORKS_AT *n..1]->(comp:Company {name: company} *10)
(person)-[:KNOWS *n..n]->(person)
```

Here we've defined that we want 35 Person nodes having a name property that will be generated, we also describe that these persons
works for one company, and that there are 7 companies.

Also, each person can know each other to implement a social like graph.

After clicking on the **Generate** button, you'll get your graph visualization.

![Imgur](http://i.imgur.com/Nb2Li64.png)

---

## Export features

You can export your graph in multiple formats or load it in a database, the available options are :

* export to GraphJson
* export to CypherQueries for usage on the Neo4j console or in the shell
* create a Neo4j console setup with your graph and open it
* load your graph in a publicly accessible database or even in your local database through the app


Discover how to describe your schema by reading the next sections.


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


---




