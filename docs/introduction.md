# Introduction

Need a ready-made graph for demo purposes? Just experimenting with Neo4j and want to see how graphs work?

Do you require a graph prototype for testing and don’t want the pain of cleaning and importing real data?

Or maybe you don’t have any data yet, or maybe it is confidential?

GraphAware GraphGen is what you're looking for!


---


## What is it ?
GraphAware GraphGen is a graph generation engine based on Cypher specification.
It creates nodes and relationships for you based on your schema definition and can also generate fake property values.


---

## Build your Graph

You only need to write a few lines of pre-written Cypher to generate your graph.

Look at the following example of a Cypher statement:

```cypher
(person:Person {name: fullName} *35)-[:WORKS_AT *n..1]->(comp:Company {name: company} *10)
(person)-[:KNOWS *n..n]->(person)
```

With the above, we have defined that we want a graph that contains 35 people (nodes of type:Person),
each having a name (property). We also defined that each of these persons works for exactly one company,
and that there are 7 companies (nodes with label `:Company`) in total.

Each person also knows every other person in the graph which means the graph will then resemble a real social network. 
Names of the people and companies will be generated at random.

Once you have clicked the Generate button, you will get your graph nicely visualised.

![Imgur](http://i.imgur.com/Nb2Li64.png)

---

## Export your graph

You can export your graph into multiple formats or load it into a database.

The following options are available:

* export to GraphJson
* export to CypherQueries for usage on the Neo4j console or in the shell
* create a Neo4j console setup with your graph and open it
* load your graph into a publicly accessible database or even in your local database through the app



GraphAware GraphGen was created by [Christophe Willemsen](https://twitter.com/ikwattro) and is maintained and supported by GraphAware.

Special thanks go to [Michael Hunger](https://twitter.com/mesirii) for his help and insights and the entire Neo4j community.

All comments, issues, and requests can be made in the respective Github repositories.

---


---




