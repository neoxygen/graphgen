## Example graph patterns examples


### Person - Company - Skill

```
(person:Person {name: fullName} *30)-[:WORKS_AT *n..1]->(company:Company {name: company, activity: bs} *10)
(person)-[:HAS_SKILL *n..n]->(skill:Skill {name: progLanguage} *10)
(company)-[:LOOKS_FOR_COMPETENCES *n..n]->(skill)
(market:Market *5)<-[:IN_MARKET *1..n]-(company)-[:LOCATED_IN *n..1]->(country:Country {name: country} *5)
```

You can view the graph on this link (don't forget to click the generate button) : [http://graphgen.neoxygen.io/?graph=M9nX5lBTO3BMom](http://graphgen.neoxygen.io/?graph=M9nX5lBTO3BMom)