# Describe your relationships

The relationship description contains three parts :

* The relationship type
* The relationship properties
* The relationship cardinality

![Imgur](http://i.imgur.com/bxya0mc.png)

## The relationship type

The type is mandatory and must contain alphabetical characters only

## The properties

The properties are defined in an inline YAML format, like for the nodes.

For a full reference, check the `Properties` section of the documentation.

## The cardinality

Cardinality is defined in the form `*n..n` .

Allowed cardinalities are `n..n`, `1..n`, `n..1` and `1..1`