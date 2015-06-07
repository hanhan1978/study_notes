# WebエンジニアのためのC言語入門ハンズオン #4

# 今日習得すること
+ C言語のデバッグ方法
+ printfマクロを使ったデバッグ演習
+ gdb/lldbを使ったデバッグ演習 
+ gdbとcoredump

# 概略
C言語における各種デバッグ方法を解説します。

特に初心者は、`printf`デバッグで何とかしようとしてしまいがちです。  
しかし、プログラムが複雑になってくると、`printf`だけではバグを追うのが辛くなります。

デバッガの使い方を覚えることで、初心者レベルを脱出しましょう。

# 1. C言語のデバッグ方法

## printf
PHPにおける`var_dump`デバッグに近いものです。使い方も簡単で、分かりやすい。  
欠点としては、プロダクションのコードに`printf`を残してはいけないということです。

そのため、デバッグ時に仕込んだ`printf`は、デバッグ完了時に取り除く必要があります。  
もし、同じ箇所でバグが発生した場合は、また入れ直しになってしまいます。  

小規模プログラムであれば充分に機能しますが、原始的ですしあまりスマートな感じはしません。

## printfマクロ
マクロを使って、`printf`を置換える方法です。

基本スタイルは`printf`デバッグと同様なのですが、  
マクロを使うことで、`printf`を撤去する必要がなくなります。

ヘッダーファイルで下記のようなマクロを定義します。
```
#define eprintf(...) fprintf(stderr, __VA_ARGS__) 
```
マクロは、コンパイル前にプリプロセッサによって置換されます。  
よって、eprintf関数は、fprintfとして動作します。

デバッグが終わったら、マクロを以下のように書き直します。
```
#define eprintf(...) {} 
```

すると、`eprintf`は何もしないコードに置き換わります。

マクロを使う方法は、ログ出力を行うコードに置換えたり等、汎用性がありそうです。  
重要な情報については、とりあえず`eprintf`で出力しておけば、動作チェックするのも楽です。

=> 今日以降、C言語プログラムの開発時は、printfを使うのは止めにして、macroを利用する方法に切り替えるのをお勧めします。  

## デバッガ
本日のメインイベントです。
変数の中身を見たり、ステップ実行したり出来ます。

以下は、代表的なC言語のデバッグです。

+ gdb  
GNUシステムの一部として、ストールマンが作成したデバッガ。  
=> 組み込み系用にリモートデバッグも可能。
+ ddd  
gdbのGUIインターフェース。機能はGDBと同様だがGUIで操作できる。
+ cgdb  
vimキーバインドで操作できるコマンドラインのgdbインターフェース。  
デバッガ作動時にソースコード表示などが出来る。
+ eclipse CDT  
IDEのgdbインターフェース。  
最近はeclipseが下火っぽいので、どれくらいメンテされているのかは分からない。
+ lldb
mac標準のllvm(clang)のデバッガ。  
llvmでコンパイルした実行可能ファイルは`gcc`とは≒となっているようで  
ある程度複雑なプログラムになってくると互換性がなく、動作しなくなります。  
=> 私調べなので、上手くやる方法もあるかもしれません。



# 2. printfマクロを使ったデバッグ

## 演習1 

```
#include <stdio.h>

#define eprintf(...) fprintf(stderr, __VA_ARGS__) 

int main(){

    printf("printf \n");

    eprintf("eprintf \n");
    eprintf("eprintf with args -> [ %s ]\n", "debug message");

}

```

### 1. コンパイル  
上記プログラムを`macro.c`という名前で保存して下さい。
コンパイルコマンドは下記です。
```
gcc -o macro macro.c
```
### 2. コマンド実行
```
./macro
```
※ `printf`, `eprintf`のいずれの行も文字列が出力されることを確認して下さい。
### 3. `eprintf`マクロを書き換える
```
#define eprintf(...) {} 
```
### 4. 再度、コンパイルして実行して下さい。
```
gcc -o macro macro.c
./macro
```
`eprintf`の行が出力されないことを確認して下さい。

# 3. gdb/lldbを使ったデバッグ演習 
ここから、デバッガを使う演習に入っていきます。

まず、デバッガを使う際の基本ですが、デバッグのための追加情報を実行可能ファイルに付加する必要があります。

## デバッグのための`gcc`オプション
+ `-g`
helpの内容をそのまま書くと  
`Generate source-level debug information`です。  
つまり、デバッグ情報を実行可能ファイルに付与してくれます。

gccでは、`-g3`とするとgdb内でmacroも使えて便利らしいですが、clang(mac標準)ではg3もgも同じだそうです。

+ `-O0`
Optimizationを0という意味です。 
最適化をしないので、デバッグがしやすくなります。

とりあえず、上の２つのオプションをコンパイル時に指定しましょう。


## 演習2 gdbのイロハ
下記コードをbasic.cという名前で保存して下さい。  
もはや、コピペでも良いのですが、良い機会なので写経をお勧めします。  
※デバッガの演習で行数が大事な要素となります。空白行も含めて、完璧に写経をお願いします。

```
#include <stdio.h>

int add(int x, int y){
    return x + y;
}


int main(){

    printf("1 \n");
    printf("2 \n");
    printf("3 \n");
    printf("4 \n");
    printf("5 \n");

    int a = 1;
    int b = 1;

    a = 2;
    a = 3;

    if(a == 3){
        b = add(a , b);
    }
}
```

### 演習2-1 デバッガの起動〜プログラム実行〜終了

1. デバッグ用にコンパイルする。
```
gcc -g -O0 basic.c -o basic
```
2. デバッガを起動する
+ gdb
```
sudo gdb basic
```
+ lldb
```
lldb basic
```
3. プログラムを実行する。
+ gdb
```
(gdb) run
```
+ lldb
```
(lldb) run
```
4. デバッガを終了する。
+ gdb
```
(gdb) quit
```
+ lldb
```
(lldb) quit
```

### 演習2-2 行数でブレークポイントを設定する
1. デバッガを起動する
+ gdb
```
sudo gdb basic
```
+ lldb
```
lldb basic
```
2. 行番号をチェックする。
+ gdb
```
(gdb) list basic.c:1,25
```
+ lldb
```
(lldb) list basic:1
```
※lldbでは、開始行数しか指定出来なかった・・・
3. 行にbreakpointをセットする。
+ gdb
```
(gdb) break basic.c:10
(gdb) b basic.c:12
```
+ lldb
```
(lldb) breakpoint set --file basic.c --line 10 
(lldb) b basic.c:12 
```
4. breakpointをチェックする
+ gdb
```
(gdb) info breakpoints
(gdb) info b
```
+ lldb
```
(lldb) breakpoint list 
(lldb) br l
```
5. 次のbreakpointまで実行する。
+ gdb
```
(gdb) continue 
```
+ lldb
```
(lldb) continue 
```
6. 次の行へ行く
+ gdb
```
(gdb) step 
```
+ lldb
```
(lldb) thread step-in 
(lldb) step 
```
7. 次の行へ行く(スタックの中には入らない)
+ gdb
```
(gdb) next 
```
+ lldb
```
(lldb) thread step-over
(lldb) next 
```
8. breakpointを削除する。
+ gdb
```
(gdb) delete 1
```
+ lldb
```
(lldb) breakpoint delete 1 
(lldb) br del 1 
```

### 演習2-3 関数でブレークポイントを設定する
1. デバッガを起動する。
+ gdb
```
sudo gdb basic
```
+ lldb
```
lldb basic
```

2. 関数名でbreakpointを設定する。
+ gdb
```
(gdb) break basic.c:add
```
+ lldb
```
(lldb) breakpoint set --name add
(lldb) b add
```
3. プログラムを実行する
+ gdb
```
(gdb) run
```
+ lldb
```
(lldb) run
```
4. 次のbreakpointまで実行する。
+ gdb
```
(gdb) continue 
```
+ lldb
```
(lldb) continue 
```

### 演習2-4 変数の中身を見る
1. デバッガを起動する。
+ gdb
```
sudo gdb basic
```
+ lldb
```
lldb basic
```
2. 関数名でbreakpointを設定する。
+ gdb
```
(gdb) break basic.c:add
```
+ lldb
```
(lldb) b add
```
3. プログラムを実行する
+ gdb
```
(gdb) run
```
+ lldb
```
(lldb) run
```
4. 変数の中身を表示する。 
+ gdb
```
(gdb) print x 
(gdb) print y 
```
+ lldb
```
(lldb) print x 
(lldb) print y 
```
5. 途中で実行を辞める
+ gdb
```
(gdb) quit 
```
ダイアログが出るので、`y`タイプ
+ lldb
```
(lldb) quit 
```
ダイアログが出るので、`y`タイプ

### 演習2-5 変数の変更を監視する
1. デバッガを起動する。
+ gdb
```
sudo gdb basic
```
+ lldb
```
lldb basic
```
2. 10行目でbreakpointを設定する。
+ gdb
```
(gdb) break basic.c:10 
```
+ lldb
```
(lldb) b basic.c:12 
```
3. プログラムを実行する。 
+ gdb
```
(gdb) run 
```
+ lldb
```
(lldb) run 
```
4. watchポイントを変数aに設定する。 
+ gdb
```
(gdb) watch a 
```
+ lldb
```
(lldb) watchpoint set variable a
```
5. プログラムを再開する。
+ gdb
```
(gdb) continue 
```
+ lldb
```
(lldb) continue 
```
※この後は、continueを何回か叩いて、変数aが変更されるたびにデバッガが停止することを確認して下さい。

## 演習3 gdb/lldbを使ったデバッグ
下記のソースコードは、バブルソートのアルゴリズムを実装しています。
実行結果は、123456789とソートされているべきですが、何故か結果がおかしいです。

gdb, lldbを使って、不具合が起こっている箇所を洗い出して下さい。
```
#include <stdio.h>

void printResult(int * arr);
int * sort(int * arr);

int main(){

    int arr[9] = {9, 8, 4, 3, 7, 6, 5, 2, 1};

    sort(arr);
    printResult(arr);
}



int * sort(int * arr){
    int size = sizeof(arr);

    for(int i = 0; i + 1 < size; i++){
        for(int k = 0; k + 1 < size ; k++){
            int tmp = arr[k];
            if(arr[k] > arr[k+1]){
                arr[k] = arr[k+1];
                arr[k+1] = tmp;
            }
        }
    }
    return arr;
}


void printResult(int * arr){
    int size = sizeof(arr);

    for(int i=0; i < size; i++){
        printf("%d ",  arr[i]);
    }
        printf("\n");
}
```

1. コンパイルする。
`sort.c`という名前でファイルを保存し、コンパイルします。
コンパイルコマンドは下記です。
```
gcc -g -O0 -o sort sort.c
```
2. 実行する
```
sort
```
結果がおかしいことを確認する。
3. デバッガで実行する。
+ gdb
```
sudo gdb sort -tui
```
+ lldb
```
lldb sort
```
4. 怪しいと思える場所に、breakpointを設定して調べる
=> 試しに色々と探してみて下さい。

# 4. gdbとcoredump
coredumpという単語を聞いたことがあるでしょうか？
LinuxOSは、SIGQUITシグナルを受け取ると、プロセスのその時点でのメモリ情報、CPUのレジスタの情報などをファイルに出力します。

よく、coreを吐くなんていう表現をします。
どうしようもない状態というイメージもありますが、異常処理が正常に行われた結果なので、それほど致命的ではないと思います。
kernel panicとかよりは、ずっとマシかと。

本題はここからですが、coredumpはgdbの入力ファイルとして使えます。
つまり、coredumpとプログラムとgdbがあれば、何故coreが吐かれたのかをチェックできます。

Webエンジニアに馴染みのあるところだと、apacheがcoreを吐いて止まる時は、これで解析できます。
=> 解析できることと、解決できることの間には大分開きがあります。

## 演習4-1 core出力の設定を行う。
1. 設定をチェックする。  
```
ulimit -a
```
`core file size`という項目の値をチェックします。`0`の場合は`core file`のサイズが0なのでcoreは出力されません。

2. core fileを出力するように設定する。  
```
ulimit -c unlimited
```
3. 再度設定をチェックする。  
```
ulimit -a
```
`core file size`が`unlimited`になっていればOK

### ulimitについて
今回の演習では、一時的に設定を変える方法を取っていますが、初期設定を`unlimited`にすることも可能です。
+ Macの場合  
`/etc/launched.conf`を作成し、設定項目を追加します。
+ Linuxの場合  
`/etc/limits.conf`の中身を編集します。

編集した結果として、`core`フィアルでディスク容量が一杯になったりします。  
基本的には不具合を調査するための一時的な変更に留めるべきと思います。

## 演習4-2 coreファイルからdebugする。

以下のプログラムをコンパイルして、実行可能ファイルを作成します。
文字列を逆順に並べ替えて、表示するプログラムです。

```
#include <stdio.h>

/*
 * この関数で文字列を逆順に並び替えています。
 */
char * reverse( char * str){
    char temp;
    int i = 0;
    temp = str[i];
    while(temp != '\0' ){
        temp = str[++i];
    }

    char * rev;

    for(int j = 0; j < i ; j++){
        rev[i-j-1] = str[j];
    }

    return rev;
}

int main(){
   char str[] = "!uoy knaht !dehsinif no sdnah gnaL C";
   char * rev = reverse(str);

   printf("%s \n", rev);
}
```

1. コンパイルする  
```
gcc -o reverse -g -O0 reverse.c
```
※ reverseという名前で実行可能ファイルが出来ていることを確認して下さい。
2. 実行し、coredumpを出力させる。
```
./reverse
```
3. coreファイルを確認する。
```
ls -l /cores
```
4. coreファイルを指定して、デバッガを起動する。
+ gdbの場合
```
sudo gdb ./reverse /cores/core.31003 -tui
```
+ lldbの場合
```
lldb ./reverse /cores/core.31003
```
5. 実行する。
+ gdbの場合
```
(gdb) run
(gdb) where
```
+ lldbの場合
```
(lldb) run
```


# 参考書籍

+ エキスパートCプログラミング―知られざるCの深層  
<img src="http://ecx.images-amazon.com/images/I/31LMc%2BpC7iL._SY344_BO1,204,203,200_.jpg" width='200'>
+ 実践 デバッグ技法 ―GDB、DDD、Eclipseによるデバッギング - Norman Matloff, Peter Salzman, 相川愛三(訳)  
<img src="http://ecx.images-amazon.com/images/I/51wdCVEYCWL.jpg" width='200'>

