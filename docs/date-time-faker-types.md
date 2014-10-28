#### unixTime

Parameters : `max = "now"`

```
{created_at: unixTime}

// 587818113
```
Parameters : `max = "now"`

#### dateTime

Parameters: `max = now`

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

#### timezone

```
{tz: timezone}

// Europe/Paris
```

#### dateTimeBetween

Parameters : `start = "-30 years"`, `end = "now"`

```
{date_of_birth: {dateTimeBetween: ["-65 years", "-18 years"]}}

// 1960-10-19 20:13:11
```

#### timezone

```
{tz: timezone}

// Europe/Paris
```

#### dayOfMonth

```
{day: dayOfMonth}

// 04
```

#### dayOfWeek

```
{day: dayOfWeek}

// Friday
```

#### month

```
{m: month}

// 04
```

#### monthName

```
{m: monthName}

// February
```

#### iso8601

```
{created: iso8601}

// 1978-12-09T10:10:29+0000
```