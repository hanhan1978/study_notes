
# WebエンジニアのためのC言語入門ハンズオン #1

# 5. 実習問題 解答
今日学んだ内容の理解を深めるための実習問題を用意しました。  

## 実習1 ポインタ
main関数内で、2つの`int`型の変数x, yを宣言して下さい。初期値は0です。
引数に2つのintのポインタを受け取る関数、goNorthとgoEastを作って下さい。

goNorthが呼ばれた場合は、yに1を足して下さい。
goEastが呼ばれた場合は、xに1を足して下さい。

goNorthを4回、goEastを3回実行した後、最後に変数x, yの内容を出力して下さい。

【解答例】
```
#include <stdio.h>

void goNorth(int * x, int * y){
    *y = *y + 1;
}

void goEast(int * x, int * y){
    *x = *x + 1;
}

int main(){
    int x = 0, y=0, i;

    for( i=0; i<4;i++){
        goNorth(&x, &y);
    }
    for( i=0; i<3;i++){
        goEast(&x, &y);
    }
    printf("x = %d, y = %d", x, y);
}
```

【出力】
```
x = 3, y = 4
```

## 実習2 配列ポインタ

文字列`ABCDE`を格納する文字列の配列を定義して下さい。
配列のポインタを利用して、先頭文字から順番に、全て小文字にして下さい。

最後に配列内容を出力して、`abcde`になることを確認して下さい。

【解答例】
```
#include <stdio.h>

int main (){
    char str[] = "ABCDE";
    char *ch;

    ch = str; //文字配列の先頭のポインタをセット &str[0]と同義

    *(ch) += 32;
    *(ch+1) += 32;
    *(ch+2) += 32;
    *(ch+3) += 32;
    *(ch+4) += 32;

    printf("str = %s\n", str);
}
```

【出力】
```
str = abcde
```

## 実習3 完全なるオマケ
`pwd`コマンドを自作してみましょう。

【解答例】
```
#include <stdio.h>
#include <unistd.h>

int main (){
    int size = 1024;
    char buf[size];

    getcwd(buf, size);

    printf("%s\n", buf);
}
```

