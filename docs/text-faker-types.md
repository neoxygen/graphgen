## Text faker types

#### word

```
{before_die: word}

// argh
```

#### words

Parameters: `$nb = 3`

```
{threelittlewords: words}

// ['porro','sed','magni']

```

#### sentence

Parameters : `nbWords = 6`

```
{status: sentence}

// Sit vitae voluptas sint non voluptates
```

#### paragraph

Parameters : `$nbSentences = 3`

```
{catchText: paragraph}

// Ut ab voluptas sed a nam. Sint autem inventore aut officia aut aut blanditiis. Ducimus eos odit amet et est ut eum.
```

#### text

Parameters : `$nbChars = 200`

```
{body: text}

// Fuga totam reiciendis qui architecto fugiat nemo. Consequatur recusandae qui cupiditate eos quod.
```

#### realText

Parameters: `maxNbChars = 200`

```
{text: realText}

// And yet I wish you could manage it?) 'And what are they made of?' Alice asked in a shrill, passionate voice. 'Would YOU like cats if you were never even spoke to Time!' 'Perhaps not,' Alice replied.

```
