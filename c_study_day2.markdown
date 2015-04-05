# WebエンジニアのためのC言語入門ハンズオン #2

注意！本内容は「WebエンジニアのためのC言語入門ハンズオン #1」をやったことを前提にしています。

# 今日習得すること
+ mallocとfree
+ 構造体 
+ データ構造の自作
+ ヘッダーファイル
+ 実習

# 前回のおさらい
## データ型
+ char
+ int
+ float
+ double
+ 上記データ型の配列
※文字列型とかありません。

## 文法
+ `if else`
+ `for while`
+ `switch`
+ `サブルーチン(function)`
+ `break continue`
+ `gotoと名札`

## 仮想アドレス空間
![仮想アドレス空間](https://raw.githubusercontent.com/hanhan1978/study_notes/master/memory.png)
今回のハンズオンでは、`ヒープ`を使います。

## ポインター
C言語では、全ての引数は値渡しです。

そのため、基本データ型(`char`,`int`,`float`,`double`)以外のデータをやり取りしたい場合は
型を指定したメモリアドレスを使って、関数間のデータの受け渡しを行います。

ポインターで利用する演算子は、主に以下の2つです。
+ 間接演算子 `*`
+ アドレス演算子 `&`

# 1. mallocとfree
## ヒープに領域を確保する
余程の小さいプログラムを除いて、プログラム内の関数間でデータをやり取りするため
C言語のプログラムでは、`malloc`という関数を使ってヒープに領域を確保して、情報を保持させます。

`malloc`は`stdlib.h`という共有ライブラリに含まれます。

`memory allocation`を略したものです。

`free`は、ヒープに確保した領域を解放します。
ヒープに確保した領域は明示的に解放しなければ、プログラムの実行が終了するまで解放されません。

デーモンプログラムなどでは、メモリの解放忘れは致命的です。
プロセスが長く生存するに従って、解放されないメモリ領域が増えていき、メモリを使い尽くしてしまいます。
これはメモリリークと呼ばれます。

`malloc`したら`free`して解放しなければなりません。2つはセットです。

とある現場では、巨大なデーモンプログラムのどこでメモリリークが発生しているのかを特定出来なかったため
３日に１回の頻度で、強制的に再起動することで、メモリの解放を行っていました。
あのプログラムはまだ動いているのだろうか・・・。


## TODO
mallocでの現実的practiceについて データ型 x サイズでのメモリ量計算等

## 演習問題 1-1
mallocを体験するプログラムです。

```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>


char * getString(){
    char * str;
    str = (char *) malloc (sizeof(char) * 8);
    strcpy(str, "abcdefg");

    return str;
}

int main (){

    char * str;
    str = getString();
    printf("str => %s\n", str);

}
```
+ `ex1-1.c`という名前で保存して下さい。
+ `gcc -o ex1-1 ex1-1.c`でコンパイルして下さい。
+ 実行ファイル`ex1-1`を実行して下さい。

### 解説
getString関数内でヒープを確保し、mainにポインターを戻しています。
このように関数間でデータの受け渡しをする場合は、ヒープを使います。

※基本データ型であれば、値渡しで受け渡しが出来ます。

## 演習問題 1-2
1-1はmallocはしたものの、freeをしてません。
main関数にfreeを追記しましょう。

```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>


char * getString(){
    char * str;
    str = (char *) malloc (sizeof(char) * 8);
    strcpy(str, "abcdefg");

    return str;
}

int main (){

    char * str;
    str = getString();
    printf("str => %s\n", str);
    free(str);
    printf("str => %s\n", str);

}
```

+ `ex1-2.c`という名前で保存して下さい。
+ `gcc -o ex1-2 ex1-2.c`でコンパイルして下さい。
+ 実行ファイル`ex1-2`を実行して下さい。

### 解説
2つ目の`println`では、何と出力されたでしょうか？

freeは、プログラムがメモリ領域を再割当て可能な状態にするという意味です。
そのため、freeされた直後であれば、メモリ内には同じ内容が残っています。

※この性質を利用して、メモリ内の情報を読取る攻撃もあります。

## 演習問題 1-3
アンチパターンとして、グローバル変数を使ってデータの受け渡しをするプログラムを書いてみましょう。

```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

char gstr[15];

void setGlobalString(){
    strcpy(gstr, "This is Global");
}

int main (){

    printf("str => %s\n", gstr);
    setGlobalString();
    printf("str => %s\n", gstr);

}
```

+ `ex1-3.c`という名前で保存して下さい。
+ `gcc -o ex1-3 ex1-3.c`でコンパイルして下さい。
+ 実行ファイル`ex1-3`を実行して下さい。

### 解説
グローバル変数を使っているので、小規模なプログラムではスッキリ見えます。
しかし、規模が大きくなるにつれ、グローバル空間を汚したことが大きな負債となって現れます。

グローバル空間は、定数で使うのみにするのが懸命と思います。

## まとめ 

+ C言語のプログラムで、基本データ型以外のデータを関数間でやり取りする場合は、ヒープを使う。
+ メモリを割り当てる時はmallocを使うが、sizeof関数を使うことで、バイト数をハードコードしないようにする。
+ 割り当てたらfreeで解放する。
+ 上手くいかないからといって、やけになってglobalを使わない。

### 余談 - メモリのオーバーコミット
`malloc`で確保できるメモリの最大量はどれくらいでしょうか？
実メモリよりも大きいメモリサイズを`malloc`で割り当て出来るのでしょうか？

答えはYesです。

64bitOS以前はメモリは貴重な存在でした。
メモリサイズが小さいため、実メモリより大きい値をmallocがセットした時にエラーにしてしまうと、
プログラムがメモリサイズに縛られてしまうことになります。

そのため、各OSはオーバーコミットという仕組みを用意していて、実メモリよりも大きいメモリサイズをプログラムが割り当てしても
エラーにならないようにしています。これはC言語の仕組みというよりは、OS側の仕様です。

あまりにも実メモリサイズとかけ離れた割り当ては、制限されるそうです。


# 2. 構造体 

構造体は、基本データ型、及び任意のポインタ
の組み合わせで構成されるユーザ定義のデータの集まりです。

C言語では、関数も関数ポインタという形で、ポインタ扱い可能です。

データを定義できて、関数をもつことができる。オブジェクト指向のクラスに近いです。
アクセス修飾子とかはないですが・・・。

構造体でデータ構造を定義出来れば、プログラムをスッキリ書くことが出来ます。
また、多すぎる引数を持つ関数があった場合にも、構造体のポインターだけを渡すことができれば、見通し良くなります。

## 構造体定義の文法

```
typedef struct <型の別名(省略可)> {
    <型に所属するデータの定義> ;
    <型に所属するデータの定義> ;
    <型に所属するデータの定義> :
} <型名>;
```

## 演習問題2-1
とりあえず構造体を作ってみましょう。

```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

typedef struct animal {
    char * type;
    char * voice;
} animal;

void printAnimal(struct animal * an){
    printf("type => %s\n", an->type);
    printf("voice => %s\n", an->voice);
}

int main(){

    struct animal * tama = (struct animal *) malloc (sizeof(struct animal));
    tama->type = (char *) malloc (1024);
    tama->voice = (char *) malloc (1024);
    strcpy((*tama).type, "cat");
    strcpy((*tama).voice, "meow");

    printAnimal(tama);

    struct animal * pochi = (struct animal *) malloc (sizeof(struct animal));
    pochi->type = (char *) malloc (1024);
    pochi->voice = (char *) malloc (1024);
    strcpy(pochi->type, "dog");
    strcpy(pochi->voice, "bowow");

    printAnimal(pochi);
}
```

+ `ex2-1.c`という名前で保存して下さい。
+ `gcc -o ex2-1 ex2-1.c`でコンパイルして下さい。
+ 実行ファイル`ex2-1`を実行して下さい。

### 解説
文字列配列を２つ持つ、animalという構造体を定義しました。
構造体を使う時のポイントは以下のとおりです。

+ 構造体自体を作成するときは、メモリ領域を確保する。
+ 構造体に定義された変数にアクセスする方法２つ、括弧を使う方法とアロー演算子


## アロー演算子について、ちょっとだけ

構造体を作成するときは、ポインターを使います。
というよりポインターでしか表現できません。

上のコードの`(*tama)`は、tamaという構造の実体を表現しています。
構造体の実体の変数に`ドット`を使ってアクセスしています。

しかし、この表記は非常に見づらいですし、記述するのも大変です。
そこで、アロー演算子が用意されています。

`(* tama).voice` = `tama->voice`

PHPのアロー演算子は、きっとココから来ているのですね。

## 演習問題2-2

2-1のプログラムにはわざとらしく欠点を残して置きました。

+ 欠点とは何でしょうか？
+ 欠点を補うためのコードを記述してみて下さい。

(解答例はハンズオン後にお見せします。)

# 3. データ構造の自作
C言語では、言語自体がサポートするデータ型が少ないので、欲しいデータ構造は自作するのが普通のようです。
そのため、他の言語では用意されているようなハッシュマップやリンクドリストのような便利なデータ構造も全て自作する必要があります。

一見、すごく面倒くさそうに見えますが、実は利点があります。

+ データ構造を自作すると、その構造が持つ計算量が把握出来る。
+ データ構造を自作すると、その構造が持つデータ量も把握出来る。

用意されている構造を使うだけでは、得られない知見を得ることが出来ます。
当ハンズオンでも、単純なデータ構造を自作してみましょう。

## 演習問題 3-1 片方向リンクドリスト
もっとも単純なデータ構造として、片方向のリンクドリストを作成します。

![片方向リンクドリスト](http://upload.wikimedia.org/wikipedia/commons/thumb/6/6d/Singly-linked-list.svg/408px-Singly-linked-list.svg.png)
[連結リスト - wikipedia](http://ja.wikipedia.org/wiki/%E9%80%A3%E7%B5%90%E3%83%AA%E3%82%B9%E3%83%88)より

```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

typedef struct linkedList {
    int value;
    struct linkedList * next;
} linkedList;

struct linkedList * createList(int value){

    struct linkedList * list = (struct linkedList *) malloc (sizeof(struct linkedList));
    list->value = value; 
    list->next = NULL; 

    return list;
}

void addList(struct linkedList * list, int value){

    struct linkedList * current;
    current = list;
    while(current->next != NULL){
        current = current->next;
    }
    current->next = createList(value);
}

void printList(struct linkedList * list){
    struct linkedList * current;
    current = list;
    while(1){
        printf("value = %d\n", current->value);
        if(current->next == NULL){
            break;
        }
        current = current->next;
    }
}

int main(){

    struct linkedList * list  = createList(15);
    addList(list, 22);
    addList(list, 5);
    addList(list, 8);

    printList(list);
}
```

+ `ex3-1.c`という名前で保存して下さい。
+ `gcc -o ex3-1 ex3-1.c`でコンパイルして下さい。
+ 実行ファイル`ex3-1`を実行して下さい。

## 演習問題3-2 再帰的に片方向リストをfreeする関数を作って下さい。
演習問題2と同じようにfreeが足りていません。
片方向リストを再帰的にfreeする関数を作ってみて下さい。

(解答例はハンズオン後にお見せします。)

# 4. ヘッダーファイル

共有ライブラリをインクルードするとき、hoge.hという名前を使いますが、hってなんでしょうか？
ｈの拡張子をもつファイルはヘッダーファイルと呼ばれます。

実際にヘッダーファイルを見てみましょう。

LinuxOSであれば、`/usr/include`の下にヘッダーファイルが入っています。
試しに`stdio.h`の中身を見てみます。

Mac OSXのstdio.hの一部抜粋
```
/* stdio buffers */
struct __sbuf {
        unsigned char   *_base;
        int             _size;
};

int      fgetc(FILE *);
int      fgetpos(FILE * __restrict, fpos_t *);
char    *fgets(char * __restrict, int, FILE *);

```

難しそうな記述が沢山見えますが、どうでしょうか？
1つずつをよく見てみると、読めそうじゃないですか？

実はヘッダーファイルは、単なるCのファイルです。

C言語では、関数は巻き上がりません。そのため、関数は使う場所よりも前で宣言されている必要があります。
しかし、全ての関数を利用する順序に並べるのは大変です。

そこで、関数を定義と実装の２つ用意して、定義部分を先に読ませてしまいます。
この定義の事をプロトタイプ宣言と呼びます。

ヘッダーファイルとは、一般的にはプロトタイプ宣言や、定数、グローバル変数をまとめた物です。
このファイルを先頭でincludeすることで、関数の未定義を防いだり、共通部分をまとめたりします。

## 演習問題4-1
プロトタイプ宣言をやってみましょう。

### まずは、プロトタイプ宣言無しで実装します。

```
#include <stdio.h>

int main(){

    int z = add(4, 5);
    printf("result is => %d\n", z);
}


int add(int x, int y){

    return x + y;
}
```

+ `ex4-1-1.c`という名前で保存して下さい。
+ `gcc -o ex4-1-1 ex4-1-1.c`でコンパイルして下さい。
+ 実行ファイル`ex4-1-1`を実行して下さい。

警告の内容を読んでみて下さい。
implicit declaration(暗黙の宣言)というのは、宣言がされていませんよ？というエラーです。

### 次に、プロトタイプ宣言を追加

```
#include <stdio.h>

int add(int x, int y);

int main(){

    int z = add(4, 5);

    printf("result is => %d\n", z);
}


int add(int x, int y){

    return x + y;
}
```

+ `ex4-1-2.c`という名前で保存して下さい。
+ `gcc -o ex4-1-2 ex4-1-2.c`でコンパイルして下さい。
+ 実行ファイル`ex4-1-2`を実行して下さい。

警告は消えたはずです。

## プロタイプ宣言の書き方
関数の`{}`を削除すれば、プロタイプ宣言の完成です。
※行末にセミコロンが必要です。

### 例

関数

```
int getTax(){
 //色々書いてある
}
```

プロタイプ宣言

```
int getTax();
```

## 演習問題4-2

プロトタイプ宣言や、グローバル変数の定義を一つのファイル
にまとめて、拡張子hでファイルを作成します。

### 演習問題4-1のコードのプロトタイプ宣言を`common.h`という名前で保存して下さい。

### そして`include文`を2行目に追記します。

```
#include "common.h"
```

+ `ex4-2.c`という名前で保存して下さい。
+ `gcc -o ex4-2 ex4-2.c`でコンパイルして下さい。
+ 実行ファイル`ex4-2`を実行して下さい。

ある程度の規模のCプログラムであれば、必ずヘッダーファイルを作る・・・と思います。
ヘッダーファイルを用意することで、複数のファイルから、関数を呼び出すことが可能となります。

# 今日のおさらい用問題 バブルソート
ここまでで、C言語の基本的な文法、お作法、ポインター、構造体まで学習しました。

おさらいとして、もっとも単純なソートアルゴリズムであるバブルソートを実装しましょう。
Cの配列を使っても良いのですが、おさらいとして、本日作成した単方向リストを使って実装しましょう。

1から実装するのは、何なので演習問題3-2をベースに作って行きます。

## ところで、バブルソートとは？
任意のコレクションのN番目の値と、N+1番目の値を比較して、大きければ値を入れ替えるソートアルゴリズムです。
大きい値が次々とコレクションの後方にソートされていく様を水の中を浮き上がる泡に例えています。

## 入力値
入力値は、演習3-1と同様とします。つまり・・・   
`15, 22, 5, 8`の順番に並んでいます。

`sortList`という名前の関数を作成して下さい。
`printList`の手前で`sortList`を呼び出して、`LinkedList`をソートします。

## 出力値
`5, 8, 15, 22`の順番に出力されたら正解です！

## 考え方のヒント
バブルソートの処理を順に考えると、LinkedListの値は、以下の様な順番で並び替わって行きます。

+ `15, 22, 5, 8`
+ `15, 5, 22, 8`
+ `15, 5, 8, 22`
+ `5, 15, 8, 22`
+ `5, 8, 15, 22`

# 参考図書
hanhan1978が学習に用いた本達です。良書厳選。

+ プログラミング言語C (K&R)  
<img src="http://ecx.images-amazon.com/images/I/41W69WGATNL.jpg" width='200'>
+ Head First C  
<img src="http://www.oreilly.co.jp/books/images/picture_large978-4-87311-609-9.jpeg" width='200'>
+ 詳説Cポインタ  
<img src="http://www.oreilly.co.jp/books/images/picture_large978-4-87311-656-3.jpeg" width='200'>
+ エキスパートCプログラミング―知られざるCの深層  
<img src="http://ecx.images-amazon.com/images/I/31LMc%2BpC7iL._SY344_BO1,204,203,200_.jpg" width='200'>
+ 定本 Cプログラマのためのアルゴリズムとデータ構造   
<img src="http://ecx.images-amazon.com/images/I/715glL2PTZL.jpg" width='200'>

# 演習問題の実装例

## 演習問題2-2
受け取った構造体の中身を１つずつfreeします。  
最後に構造体そのものもfreeします。

```
void freeAnimal(struct animal * an){
    free(an->type);
    free(an->voice);
    free(an);
}
```
## 演習問題3-2
LinkedListを順番にfreeしていきます。  
次の構造体のポインタを取得したら、現在の構造体のメモリ空間を解放します。

```
void freeList(struct linkedList * list){
    struct linkedList * cur;
    struct linkedList * nex;
    cur = list;
    while(1){
        nex = cur->next;
        free(cur);
        if(nex == NULL){
            break;
        }
        cur = nex;
    }
}
```

## ソート関数の実装例

```
int sortList(struct linkedList * list){
    struct linkedList * cur;
    struct linkedList * nex;
    cur = list;
    int changed = 0; 
    while(1){
        nex = cur->next;
        if(nex == NULL){
            break;
        }
        if(cur->value > nex->value){
            int tmp = cur->value;
            cur->value = nex->value;
            nex->value = tmp;
            changed = 1;
        }
        cur = nex; 
    }
    if (changed){
        sortList(list);
    }
    return 0;
}
```
