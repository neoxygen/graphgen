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

#### randomElement

Parameters : `array $elements = ['a','b','c']`

```
{ level: {randomElement:[[-1,2,3]] }} 

//-1
```


#### randomElements

Parameters : `array $elements = ['a','b','c']`, `$count = 1`

```
{ level: {randomElements:[[-1,2,3], 2] }}

// [-1,3]
```
