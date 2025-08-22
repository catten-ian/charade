#include <iostream>
#include <cstdio>

// 直接使用MySQL库的函数声明，不依赖头文件
#ifdef __cplusplus
extern "C" {
#endif

struct MYSQL;
MYSQL* mysql_init(MYSQL* mysql);
const char* mysql_error(MYSQL* mysql);
unsigned int mysql_errno(MYSQL* mysql);
void mysql_close(MYSQL* s);
int mysql_library_init(int argc, char **argv, char **groups);
void mysql_library_end(void);

#ifdef __cplusplus
}
#endif

int main() {
    std::cout << "=== MySQL库加载测试 ===" << std::endl;
    
    // 尝试调用MySQL库的初始化函数
    std::cout << "尝试初始化MySQL库..." << std::endl;
    int initResult = mysql_library_init(0, NULL, NULL);
    
    if (initResult != 0) {
        std::cout << "MySQL库初始化失败！错误码: " << initResult << std::endl;
        std::cout << "可能的原因：MySQL客户端库未正确安装或配置错误。" << std::endl;
        std::cout << "请确保MySQL客户端库（libmysql.dll）在系统PATH中，或与可执行文件在同一目录。" << std::endl;
        std::cout << "按任意键退出..." << std::endl;
        getchar();
        return 1;
    }
    
    std::cout << "MySQL库初始化成功！" << std::endl;
    
    // 尝试创建MySQL连接对象
    std::cout << "尝试创建MySQL连接对象..." << std::endl;
    MYSQL* conn = mysql_init(NULL);
    
    if (conn == NULL) {
        std::cout << "创建MySQL连接对象失败！" << std::endl;
        mysql_library_end();
        std::cout << "按任意键退出..." << std::endl;
        getchar();
        return 1;
    }
    
    std::cout << "MySQL连接对象创建成功！" << std::endl;
    
    // 清理资源
    mysql_close(conn);
    mysql_library_end();
    
    std::cout << "测试完成！MySQL库能够正常加载和初始化。" << std::endl;
    std::cout << "这意味着之前的问题可能与数据库连接参数或MySQL服务配置有关。" << std::endl;
    std::cout << "按任意键退出..." << std::endl;
    getchar();
    
    return 0;
}